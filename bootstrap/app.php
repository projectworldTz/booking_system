<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleImpersonation::class,
        ]);
        $middleware->alias([
            'receptionist'  => \App\Http\Middleware\ReceptionistMiddleware::class,
            'hotel.staff'   => \App\Http\Middleware\EnsureHotelStaff::class,
            'hotel.setup'   => \App\Http\Middleware\EnsureHotelOwnerHasHotel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
