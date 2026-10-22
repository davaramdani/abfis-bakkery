<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SetSessionName
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $name
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $name)
    {
        // Ganti nama cookie sesi sesuai dengan parameter yang diberikan ('admin' atau 'web')
        Config::set('session.cookie', $name . '_session');
        
        // Atur guard otentikasi default berdasarkan nama sesi
        Config::set('auth.defaults.guard', $name);

        return $next($request);
    }
}