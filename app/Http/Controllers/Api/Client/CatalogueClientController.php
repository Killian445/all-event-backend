<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Catalogue cote client authentifie.
 * Ajoute les filtres metier et les donnees enrichies de consultation.
 */
class CatalogueClientController extends Controller
{
    /**
     * Catalogue complet pour utilisateurs connectés (filtres, pagination étendue).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 20), 50);

        $query = Activite::query()
            ->where('statut', 'publiee')
            ->with(['ville:id,nom', 'categorie:id,nom', 'lieu:id,nom', 'medias:id,activite_id,url,ordre']);

        if ($request->filled('ville_id')) {
            $query->where('ville_id', $request->integer('ville_id'));
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->integer('categorie_id'));
        }
        if ($request->filled('prix_min')) {
            $query->where('prix_base', '>=', (float) $request->input('prix_min'));
        }
        if ($request->filled('prix_max')) {
            $query->where('prix_base', '<=', (float) $request->input('prix_max'));
        }
        if ($request->filled('date_debut')) {
            $date = $request->date('date_debut');
            $query->whereHas('creneaux', fn ($q) => $q->whereDate('debut_at', '>=', $date));
        }

        if ($request->filled('q')) {
            $terme = '%'.$request->string('q')->trim().'%';
            $query->where('titre', 'ilike', $terme);
        }

        $tri = $request->string('tri', 'recent')->toString();
        if ($tri === 'prix_asc') {
            $query->orderBy('prix_base');
        } elseif ($tri === 'prix_desc') {
            $query->orderByDesc('prix_base');
        } else {
            $query->latest();
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Détail + créneaux à venir (places restantes).
     */
    public function show(Activite $activite): JsonResponse
    {
        if ($activite->statut !== 'publiee') {
            return response()->json(['message' => 'Activite non disponible.'], 404);
        }

        $activite->load([
            'ville:id,nom',
            'categorie:id,nom',
            'lieu:id,nom,adresse,latitude,longitude,ville_id',
            'medias:id,activite_id,url,ordre',
            'creneaux' => fn ($q) => $q->where('statut', 'ouvert')
                ->where('debut_at', '>=', now())
                ->orderBy('debut_at'),
            'avis' => fn ($q) => $q->where('statut', 'visible')->with('user:id,name')->latest()->limit(20),
        ]);

        return response()->json($activite);
    }
}
