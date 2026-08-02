<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrency
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['BDT', 'USD', 'RUB'];

        $currency = (string) ($request->session()->get('currency') ?? $request->cookie('currency') ?? 'BDT');
        $currency = strtoupper($currency);

        if (!in_array($currency, $supported, true)) {
            $currency = 'BDT';
        }

        $request->session()->put('currency', $currency);

        return $next($request);
    }
}
