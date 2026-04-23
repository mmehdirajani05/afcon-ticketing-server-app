<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the admin panel.');
        }

        $user = Auth::user();

        if (! $user->isAdmin()) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'You do not have permission to access the admin panel.');
        }

        // Optional per-route permission check
        foreach ($permissions as $permission) {
            if (! $user->hasAdminPermission($permission)) {
                abort(403, 'Insufficient permissions.');
            }
        }

        return $next($request);
    }
}
