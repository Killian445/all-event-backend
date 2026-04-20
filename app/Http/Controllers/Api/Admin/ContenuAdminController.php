<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Categorie;
use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Gestion du   contenu fonctionnel.
 * Endpoints: categories, villes et activites globales.
 */
class ContenuAdminController extends Controller
{
    public function categories(): JsonResponse
    {
        return response()->json(Categorie::query()->orderBy('nom')->paginate(50));
    }

    public function storeCategorie(Request $request): JsonResponse
    {
        $payload = $request->validate(['nom' => ['required', 'string', 'max:255']]);
        $categorie = Categorie::create([
            'nom' => $payload['nom'],
            'slug' => Str::slug($payload['nom']).'-'.Str::lower(Str::random(4)),
        ]);
        return response()->json($categorie, 201);
    }

    public function updateCategorie(Request $request, Categorie $categorie): JsonResponse
    {
        $payload = $request->validate(['nom' => ['sometimes', 'string', 'max:255']]);
        if (isset($payload['nom'])) {
            $payload['slug'] = Str::slug($payload['nom']).'-'.Str::lower(Str::random(4));
        }
        $categorie->update($payload);
        return response()->json($categorie->fresh());
    }

    public function deleteCategorie(Categorie $categorie): JsonResponse
    {
        $categorie->delete();
        return response()->json(['message' => 'Categorie supprimee.']);
    }

    public function villes(): JsonResponse
    {
        return response()->json(Ville::query()->orderBy('nom')->paginate(50));
    }

    public function storeVille(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'code_pays' => ['nullable', 'string', 'size:2'],
        ]);
        $ville = Ville::create([
            'nom' => $payload['nom'],
            'code_pays' => strtoupper($payload['code_pays'] ?? 'CM'),
        ]);
        return response()->json($ville, 201);
    }

    public function updateVille(Request $request, Ville $ville): JsonResponse
    {
        $payload = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'code_pays' => ['nullable', 'string', 'size:2'],
        ]);
        if (isset($payload['code_pays'])) {
            $payload['code_pays'] = strtoupper($payload['code_pays']);
        }
        $ville->update($payload);
        return response()->json($ville->fresh());
    }

    public function deleteVille(Ville $ville): JsonResponse
    {
        $ville->delete();
        return response()->json(['message' => 'Ville supprimee.']);
    }

    public function activites(Request $request): JsonResponse
    {
        $query = Activite::query()
            ->with(['prestataire:id,nom', 'categorie:id,nom', 'ville:id,nom'])
            ->withCount(['creneaux', 'medias'])
            ->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        return response()->json($query->paginate(30));
    }

    public function showActivite(Activite $activite): JsonResponse
    {
        $activite->loadCount(['creneaux', 'medias']);
        $activite->load([
            'prestataire:id,nom,statut',
            'categorie:id,nom',
            'ville:id,nom',
            'lieu:id,nom',
            'medias' => fn ($q) => $q->select('id', 'activite_id', 'url', 'ordre')->orderBy('ordre'),
        ]);

        return response()->json($activite);
    }

    public function updateActivite(Request $request, Activite $activite): JsonResponse
    {
        $payload = $request->validate([
            'titre' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['sometimes', 'string', 'in:brouillon,publiee,en_attente_validation'],
            'prix_base' => ['sometimes', 'numeric', 'min:0'],
            'categorie_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'ville_id' => ['sometimes', 'integer', 'exists:villes,id'],
        ]);
        $activite->update($payload);
        return response()->json($activite->fresh()->load(['prestataire:id,nom', 'categorie:id,nom', 'ville:id,nom']));
    }

    public function deleteActivite(Activite $activite): JsonResponse
    {
        $activite->delete();
        return response()->json(['message' => 'Activite supprimee.']);
    }
}
