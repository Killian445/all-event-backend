<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Moteur simple de recommandations client.
 * Propose des activites personnalisees a partir de l'historique utilisateur.
 */
class RecommandationClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorisIds = $request->user()->favoris()->pluck('activite_id');
        $categorieIds = Activite::query()->whereIn('id', $favorisIds)->pluck('categorie_id')->unique();

        $query = Activite::query()
            ->where('statut', 'publiee')
            ->with(['ville:id,nom', 'categorie:id,nom', 'medias:id,activite_id,url,ordre']);

        if ($categorieIds->isNotEmpty()) {
            $query->whereIn('categorie_id', $categorieIds);
        }

        $activites = $query->latest()->limit(12)->get();

        return response()->json($activites);
    }
}
