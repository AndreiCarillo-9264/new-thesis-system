<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectByDepartment
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $department = auth()->user()->department;
            if ($request->path() === '/' || $request->path() === 'dashboard') {
                return redirect("/{$department}-dashboard");
            }
        }
        return $next($request);
    }
}