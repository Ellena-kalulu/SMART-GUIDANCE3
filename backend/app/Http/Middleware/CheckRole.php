<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles, true)) {
            if ($user) {
                $route = match ($user->role) {
                    'student'    => 'student.dashboard',
                    'teacher'    => 'teacher.dashboard',
                    'counsellor' => 'counsellor.dashboard',
                    'parent'     => 'parent.dashboard',
                    'admin'      => 'admin.dashboard',
                    default      => 'welcome',
                };
                return redirect()->route($route)->with('error', 'You do not have permission to access that page.');
            }
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}