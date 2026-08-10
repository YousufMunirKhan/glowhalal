<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the CONTENT locale from the URL prefix and pins it for the request.
 *
 *   /...            → 'en'       (English stays at the root)
 *   /ur-roman/...   → 'ur-Latn'  (Roman Urdu — Latin script, so it is LTR)
 *
 * Applied only to the /ur-roman route group; English routes run without it and
 * inherit the app default locale ('en' from config/app.php). Everything
 * downstream — controllers, views, the <html lang> attribute, JSON-LD
 * inLanguage — reads the pinned locale via app()->getLocale(), and views can
 * also read the shared `contentLocale`.
 *
 * NOTE: 'ur-Latn' is Roman Urdu written in Latin letters. It is NOT Arabic-script
 * Urdu, so it is left-to-right; nothing here (or in the layout) may set dir=rtl.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->is('ur-roman', 'ur-roman/*') ? 'ur-Latn' : 'en';

        app()->setLocale($locale);
        view()->share('contentLocale', $locale);

        return $next($request);
    }
}
