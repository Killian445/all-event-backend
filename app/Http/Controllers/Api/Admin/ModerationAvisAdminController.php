<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\SignalementAvis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Moderation admin des avis clients.
 * Endpoints: liste avis, signalements, changement statut et suppression.
 */
class ModerationAvisAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Avis::query()->with(['user:id,name,email', 'activite:id,titre'])->latest();
        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }
        return response()->json($query->paginate(30));
    }

    public function signalements(Request $request): JsonResponse
    {
        $query = SignalementAvis::query()
            ->with(['avis.activite:id,titre', 'user:id,name,email'])
            ->latest();
        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }
        return response()->json($query->paginate(30));
    }

    public function updateStatut(Request $request, Avis $avis): JsonResponse
    {
        $payload = $request->validate([
            'statut' => ['required', 'string', 'in:visible,masque,en_attente_moderation'],
        ]);

        $avis->update($payload);
        return response()->json($avis->fresh());
    }

    public function supprimer(Avis $avis): JsonResponse
    {
        $avis->delete();
        return response()->json(['message' => 'Avis supprime.']);
    }
}
