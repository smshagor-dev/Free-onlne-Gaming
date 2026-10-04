<?php

namespace App\Http\Controllers;

use App\Models\BanDocument;
use App\Models\Contact;
use App\Models\UserDepositDocument;
use App\Models\UserKycSubmission;
use App\Models\UserWithdrawDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        abort_if(str_contains($path, '..') || str_starts_with($path, '/'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        if (Auth::guard('admin')->check() || $this->belongsToUser($path, Auth::id())) {
            return Storage::disk('local')->download($path);
        }

        abort(403);
    }

    private function belongsToUser(string $path, ?int $userId): bool
    {
        if (Contact::whereNull('user_id')->whereIn('id', session('guest_messages', []))->where('file', $path)->exists()) {
            return true;
        }

        if (!$userId) {
            return false;
        }

        return UserKycSubmission::where('user_id', $userId)->where('value', $path)->exists()
            || Contact::where('user_id', $userId)->where('file', $path)->exists()
            || UserDepositDocument::where('value', $path)->whereHas('userDeposit', fn ($query) => $query->where('user_id', $userId))->exists()
            || UserWithdrawDocument::where('value', $path)->whereHas('withdraw', fn ($query) => $query->where('user_id', $userId))->exists()
            || BanDocument::where('user_id', $userId)->where('submit_documents', $path)->exists();
    }
}
