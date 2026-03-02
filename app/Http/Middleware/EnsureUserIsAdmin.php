<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->type_user !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return $next($request);
    }
}
