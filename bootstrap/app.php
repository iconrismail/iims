<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // Check for expiring contracts/probation daily at 08:00
        $schedule->command('contracts:check-expiry')->dailyAt('08:00');
        // Carry unused leave forward to the next year (runs on Jan 1 at 00:05)
        $schedule->command('leave:carry-forward')->yearlyOn(1, 1, '00:05');
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Railway's reverse proxy so HTTPS scheme is detected correctly
        $middleware->trustProxies(at: '*');

        // Redirect authenticated users away from guest-only routes (login)
        $middleware->redirectUsersTo('/dashboard');

        // Prevent browser from caching pages – forces fresh requests on Back button
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gracefully handle stale CSRF tokens (e.g. cached Back-button pages)
        $exceptions->renderable(function (TokenMismatchException $e, $request) {
            if ($request->user()) {
                return redirect('/dashboard');
            }
            return redirect('/login')
                ->with('error', 'Your session has expired. Please log in again.');
        });
    })
    ->create();
