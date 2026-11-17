<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function create(User $member, string $amount, string $asset, string $network, string $destination): WithdrawalRequest
    {
        return DB::transaction(function () use ($member, $amount, $asset, $network, $destination) {
            $user = User::query()->lockForUpdate()->findOrFail($member->id);
            $this->ensureEligible($user);

            $requested = $this->cents($amount);
            $this->validateAmount($requested);

            if (($asset === 'BTC' && $network !== 'bitcoin')
                || ($asset === 'USDT' && ! in_array($network, ['trc20', 'erc20'], true))) {
                throw ValidationException::withMessages(['network' => 'Unsupported asset/network combination.']);
            }

            if (WithdrawalRequest::query()->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])->exists()) {
                throw ValidationException::withMessages(['amount' => 'Finish your existing withdrawal request first.']);
            }

            // Both are checked. The available balance is a reservation source,
            // not a new credit from crypto checkout; it can never go below zero.
            if ($this->cents($user->available_balance) < $requested
                || $this->cents($user->balance) < $requested) {
                throw ValidationException::withMessages(['amount' => 'Insufficient available withdrawable balance.']);
            }

            $user->available_balance = $this->decimal($this->cents($user->available_balance) - $requested);
            $user->save();

            return WithdrawalRequest::query()->create([
                'reference' => (string) Str::uuid(),
                'user_id' => $user->id,
                'amount' => $this->decimal($requested),
                'currency' => config('withdrawals.currency'),
                'asset' => $asset,
                'network' => $network,
                'destination' => $destination,
                'status' => 'pending',
            ]);
        }, 3);
    }

    public function approve(WithdrawalRequest $request, Admin $admin): WithdrawalRequest
    {
        return $this->transition($request, function (WithdrawalRequest $record, User $user) use ($admin): void {
            if ($record->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending requests can be approved.']);
            }
            $this->ensureEligible($user);
            // Held funds are deliberately NOT transferred or credited here.
            $record->status = 'approved';
            $record->reviewed_by = $admin->id;
            $record->reviewed_at = now();
        });
    }

    public function reject(WithdrawalRequest $request, Admin $admin, string $reason): WithdrawalRequest
    {
        return $this->transition($request, function (WithdrawalRequest $record, User $user) use ($admin, $reason): void {
            if (! in_array($record->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages(['status' => 'This request is already finalized.']);
            }
            $user->available_balance = $this->decimal(
                $this->cents($user->available_balance) + $this->cents($record->amount)
            );
            $user->save();
            $record->status = 'rejected';
            $record->reviewed_by = $admin->id;
            $record->reviewed_at = now();
            $record->rejection_reason = $reason;
        });
    }

    public function cancel(WithdrawalRequest $request, User $member): WithdrawalRequest
    {
        if ($request->user_id !== $member->id) {
            abort(404);
        }

        return $this->transition($request, function (WithdrawalRequest $record, User $user): void {
            if ($record->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending requests can be cancelled.']);
            }
            $user->available_balance = $this->decimal(
                $this->cents($user->available_balance) + $this->cents($record->amount)
            );
            $user->save();
            $record->status = 'cancelled';
        });
    }

    /**
     * ADMIN ATTESTATION ONLY. The operator enters an externally completed
     * transfer reference. No automatic crypto payout or chain validation.
     * Payout records must never be represented as provider-verified transfers.
     */
    public function recordExternalTransfer(WithdrawalRequest $request, Admin $admin, string $txid): WithdrawalRequest
    {
        return $this->transition($request, function (WithdrawalRequest $record, User $user) use ($admin, $txid): void {
            if ($record->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Approve the request before recording a transfer.']);
            }
            $this->ensureEligible($user);
            if ($this->cents($user->balance) < $this->cents($record->amount)) {
                throw ValidationException::withMessages(['status' => 'Account balance changed; cannot finalize the transfer.']);
            }
            if (WithdrawalRequest::query()->where('payout_txid', $txid)->where('id', '!=', $record->id)->exists()) {
                throw ValidationException::withMessages(['txid' => 'That transfer reference has already been recorded.']);
            }

            $user->balance = $this->decimal(
                $this->cents($user->balance) - $this->cents($record->amount)
            );
            $user->save();
            $record->status = 'paid_recorded';
            $record->payout_txid = $txid;
            $record->paid_at = now();
            $record->reviewed_by = $admin->id;
        });
    }

    private function transition(WithdrawalRequest $request, callable $callback): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $callback): WithdrawalRequest {
            // Lock user before request in every transition, matching create().
            $user = User::query()->lockForUpdate()->findOrFail($request->user_id);
            $record = WithdrawalRequest::query()->lockForUpdate()->findOrFail($request->id);
            $callback($record, $user);
            $record->save();

            return $record;
        }, 3);
    }

    private function ensureEligible(User $user): void
    {
        if (! config('withdrawals.enabled')) {
            throw ValidationException::withMessages(['amount' => 'Withdrawals are currently disabled.']);
        }
        if (! $user->kyc_verified) {
            throw ValidationException::withMessages(['kyc' => 'Your KYC must be approved before requesting a withdrawal.']);
        }
        if ($user->registration_type === 'demo' || $user->is_banned || $user->trashed()) {
            throw ValidationException::withMessages(['kyc' => 'This account cannot request withdrawals.']);
        }
        $currency = strtoupper((string) ($user->currency ?? ''));
        // Never withdraw a legacy balance whose fiat denomination is
        // missing or differs from the operator's configured payout unit.
        if ($currency !== config('withdrawals.currency')) {
            throw ValidationException::withMessages(['amount' => 'Account currency differs from the payout currency.']);
        }
    }

    private function validateAmount(int $amount): void
    {
        if ($amount < $this->cents((string) config('withdrawals.minimum'))
            || $amount > $this->cents((string) config('withdrawals.maximum'))) {
            throw ValidationException::withMessages(['amount' => 'Amount is outside the allowed withdrawal range.']);
        }
    }

    private function cents(string|int|float|null $amount): int
    {
        $value = (string) ($amount ?? 0);
        if (! preg_match('/^\d{1,12}(?:\.\d{1,2})?$/D', $value)) {
            throw ValidationException::withMessages(['amount' => 'Invalid amount format.']);
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $whole) * 100 + (int) str_pad($fraction, 2, '0');
    }

    private function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
