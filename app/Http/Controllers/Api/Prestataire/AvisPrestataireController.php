<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des avis recu par prestataire.
 * Endpoints: consultation et reponse officielle a un avis.
 */
class AvisPrestataireController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');

        $avis = Avis::query()
            ->whereHas('activite', fn ($q) => $q->whereIn('prestataire_id', $prestataireIds))
            ->with([
                'user:id,name,email',
                'activite:id,titre,ville_id',
                'activite.ville:id,nom',
            ])
            ->latest()
            ->paginate(30);

        return response()->json($avis);
    }

    public function repondre(Request $request, Avis $avis): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');
        if (! $avis->activite()->whereIn('prestataire_id', $prestataireIds)->exists()) {
            return response()->json(['message' => 'Avis introuvable.'], 404);
        }

        $payload = $request->validate([
            'reponse_prestataire' => ['nullable', 'string', 'max:5000'],
        ]);

        $texte = trim((string) ($payload['reponse_prestataire'] ?? ''));
        if ($texte === '') {
            $avis->update([
                'reponse_prestataire' => null,
                'repondu_le' => null,
            ]);
        } else {
            $avis->update([
                'reponse_prestataire' => $texte,
                'repondu_le' => now(),
            ]);
        }

        return response()->json(
            $avis->fresh()->load([
                'user:id,name,email',
                'activite:id,titre,ville_id',
                'activite.ville:id,nom',
            ])
        );
    }
}
