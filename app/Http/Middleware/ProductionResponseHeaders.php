<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class ProductionResponseHeaders
{
    private const PROVIDER_ERROR_MESSAGE = 'The game service is temporarily unavailable. Please try again.';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure() && config('security.hsts.enabled')) {
            $value = 'max-age='.(int) config('security.hsts.max_age', 31536000);
            if (config('security.hsts.include_subdomains')) {
                $value .= '; includeSubDomains';
            }
            if (config('security.hsts.preload')) {
                $value .= '; preload';
            }
            $response->headers->set('Strict-Transport-Security', $value);
        }

        if ($this->isPrivate($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        if (config('security.sanitize_provider_errors', true) && $this->isCasinoProviderAction($request)) {
            $this->sanitizeFlashedProviderError($request);
        }

        return $response;
    }

    private function isPrivate(Request $request): bool
    {
        if ($request->user()) {
            return true;
        }

        return $request->is(
            'user/*',
            'sm-shagor/free-games/admin-main/control-back-office/*',
            'api/*',
            'auth/*',
            'login',
            'logout',
            'register',
            'registration',
            'verify',
            '2fa/*',
            'password/*',
            'forgot-password',
            'search-user',
            'private-files/*',
            'demo'
        );
    }

    private function isCasinoProviderAction(Request $request): bool
    {
        return $request->is(
            'casino/play',
            'casino/session/*',
            'bonus-play/play',
            'bonus-play/session/*',
            'casino-cashback/play',
            'casino-cashback/session/*',
            'vip-bonus-play/play',
            'vip-bonus-play/session/*'
        );
    }

    private function sanitizeFlashedProviderError(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $session = $request->session();

        if ($session->has('error')) {
            $value = (string) $session->get('error');
            if ($this->looksInternal($value)) {
                $this->logSuppressed($request, $value);
                $session->flash('error', self::PROVIDER_ERROR_MESSAGE);
            }
        }

        if ($session->has('notify')) {
            $notify = $session->get('notify');
            if (is_array($notify) && isset($notify[0], $notify[1]) && $notify[0] === 'error') {
                $value = (string) $notify[1];
                if ($this->looksInternal($value)) {
                    $this->logSuppressed($request, $value);
                    $session->flash('notify', ['error', self::PROVIDER_ERROR_MESSAGE]);
                }
            }
        }
    }

    private function looksInternal(string $message): bool
    {
        $safe = [
            'Game ID is required.',
            'Game URL not returned from API.',
            'Failed to open game.',
        ];

        return $message !== '' && ! in_array($message, $safe, true);
    }

    private function logSuppressed(Request $request, string $message): void
    {
        Log::warning('Casino provider error suppressed from user response.', [
            'path' => $request->path(),
            'user_id' => $request->user()?->id,
            'error_fingerprint' => hash('sha256', $message),
        ]);
    }
}
