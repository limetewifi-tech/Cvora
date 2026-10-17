<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerifiedWhenEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('cvora.must_verify_email')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && ! $user->hasVerifiedEmail() && ! $request->routeIs('verification.*', 'logout')) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
