<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\ActiviteMedia;
use App\Services\Media\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Gestion des medias d'activite cote prestataire.
 * Endpoints: ajout (URL/fichier) et suppression locale + Cloudinary.
 */
class MediaActivitePrestataireController extends Controller
{
    public function ajouter(Request $request, Activite $activite): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');
        if (! in_array($activite->prestataire_id, $prestataireIds->all(), true)) {
            return response()->json(['message' => 'Activite introuvable.'], 404);
        }

        $payload = $request->validate([
            'url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:5120'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($payload['url']) && ! $request->hasFile('image')) {
            return response()->json(['message' => 'Fournir une url ou un fichier image.'], 422);
        }

        $url = $payload['url'] ?? null;
        $publicId = null;
        if ($request->hasFile('image')) {
            try {
                $uploaded = app(CloudinaryService::class)->uploadImage(
                    $request->file('image'),
                    "allevent/activites/{$activite->id}"
                );
            } catch (RuntimeException $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            $url = $uploaded['secure_url'] ?? $uploaded['url'] ?? null;
            $publicId = $uploaded['public_id'] ?? null;
        }

        $media = ActiviteMedia::create([
            'activite_id' => $activite->id,
            'url' => $url,
            'ordre' => $payload['ordre'] ?? 0,
            'cloudinary_public_id' => $publicId,
        ]);

        $activite->refresh()->repasserEnRevalidationSiPubliee();

        return response()->json($media, 201);
    }

    public function supprimer(Request $request, Activite $activite, ActiviteMedia $media): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');
        if (! in_array($activite->prestataire_id, $prestataireIds->all(), true) || $media->activite_id !== $activite->id) {
            return response()->json(['message' => 'Media introuvable.'], 404);
        }

        if ($media->cloudinary_public_id) {
            app(CloudinaryService::class)->destroyImage($media->cloudinary_public_id);
        }
        $media->delete();
        $activite->refresh()->repasserEnRevalidationSiPubliee();

        return response()->json(['message' => 'Media supprime.']);
    }
}
