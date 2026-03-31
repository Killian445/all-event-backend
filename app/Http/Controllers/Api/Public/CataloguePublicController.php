<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Categorie;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Catalogue public sans authentification.
 * Expose landing, listing activites, details, avis, categories et villes.
 */
class CataloguePublicController extends Controller
{
    public function landing(): JsonResponse
    {
        return response()->json([
            'message' => 'Bienvenue sur ALLEVENT.',
            'invitation_prestataire' => 'Inscrivez vos activites pour toucher plus de clients.',
        ]);
    }

    public function activites(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 12), 48);

        $query = Activite::query()
            ->where('statut', 'publiee')
            ->with(['ville:id,nom', 'categorie:id,nom', 'medias:id,activite_id,url,ordre'])
            ->withCount(['avis as avis_visible_count' => fn ($q) => $q->where('statut', 'visible')])
            ->latest();

        if ($request->filled('ville_id')) {
            $query->where('ville_id', (int) $request->input('ville_id'));
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', (int) $request->input('categorie_id'));
        }

        if ($request->filled('q')) {
            $needle = trim((string) $request->input('q'));
            if ($needle !== '') {
                $like = '%'.addcslashes($needle, '%_\\').'%';
                $query->where('titre', 'like', $like);
            }
        }

        if ($request->filled('prix_min')) {
            $query->where('prix_base', '>=', (float) $request->input('prix_min'));
        }

        if ($request->filled('prix_max')) {
            $query->where('prix_base', '<=', (float) $request->input('prix_max'));
        }

        $activites = $query->paginate($perPage);

        return response()->json($activites);
    }

    public function showActivite(Activite $activite): JsonResponse
    {
        if ($activite->statut !== 'publiee') {
            return response()->json(['message' => 'Activite non disponible publiquement.'], 404);
        }

        $activite->load([
            'ville:id,nom',
            'categorie:id,nom',
            'lieu:id,nom,adresse,ville_id',
            'medias:id,activite_id,url,ordre',
            'creneaux' => fn ($q) => $q->where('statut', 'ouvert')
                ->where('capacite_restante', '>', 0)
                ->orderBy('debut_at'),
            'avis' => fn ($q) => $q->where('statut', 'visible')->with('user:id,name')->latest()->limit(20),
        ]);

        return response()->json($activite);
    }

    public function avisActivite(Activite $activite): JsonResponse
    {
        if ($activite->statut !== 'publiee') {
            return response()->json(['message' => 'Activite non disponible publiquement.'], 404);
        }

        $avis = $activite->avis()
            ->where('statut', 'visible')
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($avis);
    }

    public function categories(): JsonResponse
    {
        return response()->json(Categorie::query()->select('id', 'nom', 'slug')->orderBy('nom')->get());
    }

    public function villes(): JsonResponse
    {
        return response()->json(Ville::query()->select('id', 'nom', 'code_pays')->orderBy('nom')->get());
    }
}
