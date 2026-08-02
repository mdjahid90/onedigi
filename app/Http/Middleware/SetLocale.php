<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'bn', 'ru'];

        $locale = $request->session()->get('locale');
        if (!is_string($locale) || !in_array($locale, $supported, true)) {
            $locale = $request->cookie('locale');
        }

        if (!is_string($locale) || !in_array($locale, $supported, true)) {
            $locale = (string) config('app.locale', 'en');
        }

        if (!in_array($locale, $supported, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
