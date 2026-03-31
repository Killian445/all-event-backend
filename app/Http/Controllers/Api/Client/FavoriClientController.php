<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Favori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des favoris client.
 * Endpoints: lister, ajouter et retirer des activites favorites.
 */
class FavoriClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = Favori::query()
            ->where('user_id', $request->user()->id)
            ->with(['activite' => fn ($q) => $q->where('statut', 'publiee')->with(['ville:id,nom', 'categorie:id,nom', 'medias'])])
            ->latest()
            ->paginate(30);

        return response()->json($data);
    }

    public function ajouter(Request $request): JsonResponse
    {
        $donnees = $request->validate([
            'activite_id' => ['required', 'integer', 'exists:activites,id'],
        ]);

        $activite = Activite::query()->findOrFail($donnees['activite_id']);
        if ($activite->statut !== 'publiee') {
            return response()->json(['message' => 'Activite non disponible.'], 422);
        }

        $favori = Favori::query()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'activite_id' => $activite->id,
            ]
        );

        return response()->json($favori->load('activite'), $favori->wasRecentlyCreated ? 201 : 200);
    }

    public function supprimer(Request $request, Activite $activite): JsonResponse
    {
        Favori::query()
            ->where('user_id', $request->user()->id)
            ->where('activite_id', $activite->id)
            ->delete();

        return response()->json(['message' => 'Favori retire.']);
    }
}
