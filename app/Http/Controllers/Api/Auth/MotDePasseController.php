<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Flux de mot de passe oublie.
 * Endpoints: demande lien de reset puis reinitialisation du mot de passe.
 */
class MotDePasseController extends Controller
{
    public function demanderLienReset(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function reinitialiser(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $passwordResetOk = false;
        $status = Password::reset(
            $payload,
            function ($user, string $password) use (&$passwordResetOk): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
                $passwordResetOk = true;
            }
        );

        if (! $passwordResetOk) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json(['message' => __($status)]);
    }
}