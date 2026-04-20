<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbaseIdentityController extends Controller
{
    public function token(Request $request): JsonResponse
    {
        $secret = (string) config('services.chatbase.identity_secret');
        if ($secret === '') {
            return response()->json([
                'message' => 'CHATBASE_IDENTITY_SECRET non configure.',
            ], 500);
        }

        $user = $request->user();
        $now = time();
        $exp = $now + 3600;

        $payload = [
            'user_id' => (string) $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $exp,
        ];

        return response()->json([
            'token' => $this->signJwtHs256($payload, $secret),
            'expires_at' => $exp,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function signJwtHs256(array $payload, string $secret): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headerBase64 = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $payloadBase64 = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $unsigned = $headerBase64.'.'.$payloadBase64;
        $signature = hash_hmac('sha256', $unsigned, $secret, true);

        return $unsigned.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

