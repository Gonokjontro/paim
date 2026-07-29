<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Admin has access to everything
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Check if user role is permitted
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // If Viewer attempts POST/PUT/DELETE or unauthorized operation
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
            return back()->with('error', "Access Denied: Your current role is '" . ucfirst($userRole) . "'. Only Admins or Managers can perform this action.");
        }

        abort(403, "Access Denied: Your role '" . ucfirst($userRole) . "' does not have permission to view this section.");
    }
}
