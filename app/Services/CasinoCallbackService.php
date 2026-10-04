<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CasinoCallbackService
{
    public function handle(Request $request, string $providerName): JsonResponse
    {
        if (!$request->isMethod('post')) {
            return $this->fail('method_not_allowed', 405);
        }

        $provider = $this->provider($providerName);
        $cmd = (string) $request->input('cmd');
        $rules = $this->rulesFor($cmd);

        if ($provider === [] || blank($provider['hall'] ?? null) || blank($provider['key'] ?? null)) {
            $this->logRejected($request, $providerName, 'provider_not_configured');
            return $this->fail('invalid_credentials', 403);
        }

        if ($rules === []) {
            $this->logRejected($request, $providerName, 'invalid_command');
            return $this->fail('invalid_command', 400);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->logRejected($request, $providerName, 'invalid_payload');
            return $this->fail('invalid_payload', 400);
        }

        if (!$this->authenticated($request, $provider)) {
            $this->logRejected($request, $providerName, 'invalid_provider_credentials');
            return $this->fail('invalid_credentials', 403);
        }

        return match ($cmd) {
            'getBalance' => $this->getBalance($request, $provider),
            'writeBet' => $this->writeBet($request, $providerName, $provider),
            default => $this->fail('invalid_command', 400),
        };
    }

    private function getBalance(Request $request, array $provider): JsonResponse
    {
        $user = User::where('user_id', $request->input('login'))->first();

        if (!$user) {
            return $this->fail('user_not_found');
        }

        return response()->json([
            'status' => 'success',
            'error' => '',
            'login' => $request->input('login'),
            'balance' => $this->formatMoney($this->balance($user, $provider)),
            'currency' => config('casino.currency', 'BDT'),
        ]);
    }

    private function writeBet(Request $request, string $providerName, array $provider): JsonResponse
    {
        return DB::transaction(function () use ($request, $providerName, $provider): JsonResponse {
            $login = (string) $request->input('login');
            $tradeId = (string) ($request->input('tradeId') ?? $request->input('transactionId'));
            $operation = $request->input('betInfo') === 'refund' ? 'refund' : 'writeBet';

            $user = User::where('user_id', $login)->lockForUpdate()->first();

            if (!$user) {
                return $this->fail('user_not_found');
            }

            $existing = Transaction::where('provider_name', $providerName)
                ->where('provider_transaction_id', $tradeId)
                ->where('provider_action', $operation)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $this->success($login, $this->balance($user, $provider), (string) $existing->id);
            }

            $bet = (float) $request->input('bet');
            $win = (float) $request->input('win');

            if ($operation === 'refund') {
                $refundField = $provider['refund_field'];
                $user->{$refundField} = (float) $user->{$refundField} + $bet;
            } else {
                if ($bet > $this->balance($user, $provider)) {
                    return $this->fail('fail_balance');
                }

                $this->debit($user, $provider, $bet);

                if ($win > 0) {
                    $winField = $provider['win_field'];
                    $user->{$winField} = (float) $user->{$winField} + $win;
                }
            }

            $user->save();

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'session_id' => $request->input('sessionId') ?? $request->input('sessionid'),
                'amount' => $bet,
                'trx_type' => $operation === 'refund' || $win > 0 ? '+' : '-',
                'trx' => $tradeId,
                'transaction_type' => 'casino game',
                'remark' => 'casino',
                'comments' => $operation === 'refund' ? 'Casino Bet Refund' : ($provider['transaction_comment'] ?? 'casino game bet'),
                'status' => 'approved',
                'transaction_number' => $tradeId,
                'casino_details' => json_encode($this->safePayload($request), JSON_UNESCAPED_SLASHES),
                'provider_name' => $providerName,
                'provider_transaction_id' => $tradeId,
                'provider_action' => $operation,
            ]);

            return $this->success($login, $this->balance($user, $provider), (string) $transaction->id);
        });
    }

    private function debit(User $user, array $provider, float $amount): void
    {
        $remaining = $amount;

        foreach ($provider['balance_fields'] as $field) {
            if ($remaining <= 0) {
                return;
            }

            $available = (float) $user->{$field};
            $deduct = min($available, $remaining);
            $user->{$field} = $available - $deduct;
            $remaining -= $deduct;
        }
    }

    private function balance(User $user, array $provider): float
    {
        return array_reduce($provider['balance_fields'], function (float $total, string $field) use ($user): float {
            return $total + (float) $user->{$field};
        }, 0.0);
    }

    private function provider(string $providerName): array
    {
        return config("casino.providers.{$providerName}", []);
    }

    private function authenticated(Request $request, array $provider): bool
    {
        return hash_equals((string) $provider['hall'], (string) $request->input('hall'))
            && hash_equals((string) $provider['key'], (string) $request->input('key'));
    }

    private function rulesFor(string $cmd): array
    {
        $base = [
            'cmd' => 'required|in:getBalance,writeBet',
            'hall' => 'required',
            'key' => 'required|string',
            'login' => 'required|string|max:255',
        ];

        if ($cmd === 'getBalance') {
            return $base;
        }

        if ($cmd === 'writeBet') {
            return $base + [
                'bet' => 'required|numeric|min:0',
                'win' => 'required|numeric|min:0',
                'tradeId' => 'required_without:transactionId|string|max:255',
                'transactionId' => 'required_without:tradeId|string|max:255',
                'betInfo' => 'nullable|string|max:255',
                'gameId' => 'required|integer',
                'sessionId' => 'required_without:sessionid|integer',
                'sessionid' => 'required_without:sessionId|integer',
                'date' => 'nullable|date_format:Y-m-d H:i:s',
            ];
        }

        return [];
    }

    private function safePayload(Request $request): array
    {
        return Arr::except($request->all(), ['key', 'sign', 'signature']);
    }

    private function logRejected(Request $request, string $providerName, string $reason): void
    {
        Log::warning('Rejected casino callback', [
            'provider' => $providerName,
            'reason' => $reason,
            'ip' => $request->ip(),
            'cmd' => $request->input('cmd'),
            'hall' => $request->input('hall'),
            'login_hash' => $request->filled('login') ? hash('sha256', (string) $request->input('login')) : null,
            'trade_id' => $request->input('tradeId') ?? $request->input('transactionId'),
        ]);
    }

    private function success(string $login, float $balance, string $operationId): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'login' => $login,
            'balance' => $this->formatMoney($balance),
            'currency' => config('casino.currency', 'BDT'),
            'operationId' => $operationId,
        ]);
    }

    private function fail(string $error, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'fail',
            'error' => $error,
        ], $status);
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
