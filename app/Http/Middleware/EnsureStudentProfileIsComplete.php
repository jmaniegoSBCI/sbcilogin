<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()->role === 'student', 403);

        if (! $request->user()->profileIsComplete()) {
            return redirect()->route('profile.setup');
        }

        return $next($request);
    }
}
