<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // GLOBAL (not web-group): must run even for legacy URLs that match no
        // route on the new site (e.g. /category/skincare), which never reach the
        // web group. Resolves the redirects table + trailing slashes in one 301
        // hop, before routing. See docs/seo-migration-and-content-plan.md §A2.
        $middleware->prepend(\App\Http\Middleware\LegacyRedirectMiddleware::class);

        // The cookie-consent choice is set client-side (plain JS cookie), so it
        // must be exempt from Laravel's cookie encryption or request()->cookie()
        // can't read it — which would keep Google Analytics from ever loading.
        $middleware->encryptCookies(except: ['cookie_consent']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
