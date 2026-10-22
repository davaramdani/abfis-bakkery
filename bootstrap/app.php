<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Application as AppConfig;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login') : route('login'));
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.dashboard') : route('user.dashboard'));
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdmin::class,
            'session' => \App\Http\Middleware\SetSessionName::class,
            'auth:admin' => \Illuminate\Auth\Middleware\Authenticate::class . ':admin',
            'auth:web' => \Illuminate\Auth\Middleware\Authenticate::class . ':web',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
