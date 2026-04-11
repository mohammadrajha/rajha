<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? session('locale')
            ?? $request->getPreferredLanguage(['en', 'ar'])
            ?? 'en';

        app()->setLocale($locale);

        return $next($request);
    }
}
