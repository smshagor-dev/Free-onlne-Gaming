<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\TwoFactorMiddleware;
use App\Http\Middleware\CheckAccountStatus;
use App\Http\Middleware\RestrictDemoFinancialActions;

$webRoutes = __DIR__.'/../routes/web.php';
$apiRoutes = __DIR__.'/../routes/api.php';
$consoleRoutes = __DIR__.'/../routes/console.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: file_exists($webRoutes) ? $webRoutes : null,
        api: file_exists($apiRoutes) ? $apiRoutes : null,
        commands: file_exists($consoleRoutes) ? $consoleRoutes : null,
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES'),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $trustedHosts = array_filter(array_map('trim', explode(',', (string) env('TRUSTED_HOSTS', ''))));
        if ($trustedHosts !== []) {
            $middleware->trustHosts(
                at: fn (): array => array_map(
                    fn (string $host): string => str_starts_with($host, '^') ? $host : '^'.preg_quote($host).'$',
                    $trustedHosts,
                ),
                subdomains: false,
            );
        } else {
            $middleware->trustHosts();
        }

        $middleware->redirectGuestsTo(function (Request $request): string {
            return $request->is('sm-shagor/free-games/admin-main/control-back-office/*')
                ? route('admin.login')
                : route('login.form');
        });
        $middleware->appendToGroup('web', RestrictDemoFinancialActions::class);
        $middleware->alias([
            '2fa' => TwoFactorMiddleware::class,
            'ban' => CheckAccountStatus::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'checkprofile' => \App\Http\Middleware\CheckProfile::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
