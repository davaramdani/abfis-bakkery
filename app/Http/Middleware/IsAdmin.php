<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login via guard 'admin' DAN rolenya adalah 'admin'
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role == 'admin') {
            return $next($request);
        }
        
        // Jika tidak, kembalikan ke halaman login admin
        return redirect()->route('admin.login');
    }
}