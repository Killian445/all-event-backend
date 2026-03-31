<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampagnePublicitaire;
use App\Models\PaiementPublicite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Administration des publicites.
 * Endpoints: CRUD campagnes, validation statuts et lecture paiements pub.
 */
class GestionPubliciteAdminController extends Controller
{
    public function storeCampagne(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'prestataire_id' => ['nullable', 'integer', 'exists:prestataires,id'],
            'titre' => ['required', 'string', 'max:255'],
            'emplacement' => ['required', 'string', 'max:64'],
            'ville_id' => ['nullable', 'integer', 'exists:villes,id'],
            'categorie_id' => ['nullable', 'integer', 'exists:categories,id'],
            'activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'debut_at' => ['required', 'date'],
            'fin_at' => ['required', 'date', 'after:debut_at'],
            'priorite' => ['nullable', 'integer', 'min:0'],
            'budget_montant' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['nullable', 'string', 'in:validee,refusee,en_attente_validation,active,inactive'],
        ]);

        $campagne = CampagnePublicitaire::create($payload);
        return response()->json($campagne, 201);
    }

    public function campagnes(): JsonResponse
    {
        $data = CampagnePublicitaire::query()
            ->with(['prestataire:id,nom', 'activite:id,titre', 'ville:id,nom', 'categorie:id,nom'])
            ->latest()
            ->paginate(30);

        return response()->json($data);
    }

    public function updateStatutCampagne(Request $request, CampagnePublicitaire $campagne): JsonResponse
    {
        $payload = $request->validate([
            'statut' => ['required', 'string', 'in:validee,refusee,en_attente_validation,active,inactive'],
        ]);

        $campagne->update($payload);
        return response()->json($campagne->fresh());
    }

    public function updateCampagne(Request $request, CampagnePublicitaire $campagne): JsonResponse
    {
        $payload = $request->validate([
            'titre' => ['sometimes', 'string', 'max:255'],
            'emplacement' => ['sometimes', 'string', 'max:64'],
            'ville_id' => ['nullable', 'integer', 'exists:villes,id'],
            'categorie_id' => ['nullable', 'integer', 'exists:categories,id'],
            'activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'debut_at' => ['sometimes', 'date'],
            'fin_at' => ['sometimes', 'date'],
            'priorite' => ['sometimes', 'integer', 'min:0'],
            'budget_montant' => ['nullable', 'numeric', 'min:0'],
        ]);

        $campagne->update($payload);
        return response()->json($campagne->fresh());
    }

    public function deleteCampagne(CampagnePublicitaire $campagne): JsonResponse
    {
        $campagne->delete();
        return response()->json(['message' => 'Campagne supprimee.']);
    }

    public function paiementsPublicite(): JsonResponse
    {
        $data = PaiementPublicite::query()
            ->with(['campagne:id,titre,statut'])
            ->latest()
            ->paginate(30);

        return response()->json($data);
    }
}
