<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifierRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }

        $rolesAutorises = array_map('trim', explode(',', $roles));
        if (! in_array($user->role, $rolesAutorises, true)) {
            return response()->json(['message' => 'Acces non autorise pour ce role.'], 403);
        }

        return $next($request);
    }
}
