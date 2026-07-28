<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/ussd/*',
            'ussd/simulate',
        ]);

        // When an already-authenticated user visits a guest-only route (login, register),
        // redirect them to their role-specific dashboard instead of the welcome page.
        $middleware->redirectUsersTo(function () {
            $user = Auth::user();
            if (!$user) {
                return route('welcome');
            }
            return match ($user->role) {
                'student'    => route('student.dashboard'),
                'teacher'    => route('teacher.dashboard'),
                'counsellor' => route('counsellor.dashboard'),
                'parent'     => route('parent.dashboard'),
                'admin'      => route('admin.dashboard'),
                default      => route('welcome'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
