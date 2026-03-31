<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Profil;
use App\Services\Media\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Gestion du profil client.
 * Endpoints: consultation et mise a jour des informations + avatar Cloudinary.
 */
class ProfilClientController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profil', 'prestataires');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'profil' => $user->profil,
            'prestataires' => $user->prestataires,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $donnees = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'prenom' => ['nullable', 'string', 'max:255'],
            'nom' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:32'],
            'avatar' => ['nullable', 'string', 'max:2048'],
            'avatar_file' => ['nullable', 'image', 'max:5120'],
        ]);

        $user->fill(array_intersect_key($donnees, array_flip(['name', 'email'])));
        $user->save();

        $profil = Profil::query()->firstOrCreate(['user_id' => $user->id], ['user_id' => $user->id]);
        $champsProfil = array_intersect_key($donnees, array_flip(['prenom', 'nom', 'telephone', 'avatar']));

        if ($request->hasFile('avatar_file')) {
            try {
                $uploaded = app(CloudinaryService::class)->uploadImage(
                    $request->file('avatar_file'),
                    "allevent/avatars/{$user->id}"
                );
            } catch (RuntimeException $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            if (! empty($profil->avatar_public_id)) {
                app(CloudinaryService::class)->destroyImage($profil->avatar_public_id);
            }

            $champsProfil['avatar'] = $uploaded['secure_url'] ?? $uploaded['url'] ?? $profil->avatar;
            $champsProfil['avatar_public_id'] = $uploaded['public_id'] ?? null;
        }

        if ($champsProfil !== []) {
            $profil->fill($champsProfil);
            $profil->save();
        }

        return response()->json($user->fresh()->load('profil'));
    }
}
