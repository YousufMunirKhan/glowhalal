<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the `redirects` table and canonicalises trailing slashes.
 *
 * Runs FIRST in the web group (prepended in bootstrap/app.php), before routing,
 * so a legacy URL resolves in a SINGLE 301 hop straight to its final target —
 * even when the incoming request carries a trailing slash (the lookup is done on
 * the slash-trimmed path). This is what makes the WordPress → Laravel migration
 * safe: every old URL 301s exactly once to a live page, never redirect→404 and
 * never a chain.
 *
 * Order of resolution:
 *   1. redirects-table match (exact, on the normalised path) -> 301/302, or 410
 *      when status_code is 410 (WordPress cruft that should be marked Gone).
 *   2. trailing-slash canonical: any non-root path ending in "/" 301s to the
 *      slash-less form, preserving the query string.
 *
 * Redirect rows are managed in the Filament "Redirects" resource and seeded by
 * RedirectSeeder.
 */
class LegacyRedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only GET/HEAD get redirected; never rewrite a POST (form submits).
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = rawurldecode($request->getPathInfo());   // e.g. "/Shop/"
        $trimmed = rtrim($path, '/');                     // "/Shop"  (root -> "")
        $lookup = strtolower($trimmed === '' ? '/' : $trimmed);

        // 1) Redirect table — exact match on the normalised path, one hop to final.
        //    Wrapped defensively: this is GLOBAL middleware on every request, so a
        //    DB outage must fall through to the app, never 500 the whole site.
        if ($lookup !== '/') {
            try {
                $redirect = Redirect::query()
                    ->where('is_active', true)
                    ->where('from_path', $lookup)
                    ->first();
            } catch (\Throwable $e) {
                $redirect = null;
            }

            if ($redirect) {
                // Best-effort hit accounting; never let it break the redirect.
                try {
                    $redirect->forceFill([
                        'hits' => $redirect->hits + 1,
                        'last_hit_at' => now(),
                    ])->saveQuietly();
                } catch (\Throwable $e) {
                    // ignore — analytics only
                }

                if ((int) $redirect->status_code === 410) {
                    return response('Gone', 410);
                }

                return redirect()->to($redirect->to_path, $redirect->status_code);
            }
        }

        // 2) Trailing-slash canonical — one 301 to the slash-less form.
        if ($path !== '/' && str_ends_with($path, '/')) {
            $query = $request->getQueryString();

            return redirect()->to(($trimmed !== '' ? $trimmed : '/').($query ? '?'.$query : ''), 301);
        }

        return $next($request);
    }
}
