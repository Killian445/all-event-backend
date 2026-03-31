<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des promotions prestataire.
 * Endpoints: lister, creer et modifier des reductions.
 */
class GestionPromotionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');

        return response()->json(
            Promotion::query()->whereIn('prestataire_id', $prestataireIds)->latest()->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => ['nullable', 'string', 'max:64', 'unique:promotions,code'],
            'libelle' => ['required', 'string', 'max:255'],
            'prestataire_id' => ['required', 'integer', 'exists:prestataires,id'],
            'activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'type_remise' => ['required', 'string', 'max:32'],
            'valeur' => ['required', 'numeric', 'min:0'],
            'montant_minimum_commande' => ['nullable', 'numeric', 'min:0'],
            'reduction_plafond' => ['nullable', 'numeric', 'min:0'],
            'utilisations_max' => ['nullable', 'integer', 'min:1'],
            'debut_at' => ['required', 'date'],
            'fin_at' => ['required', 'date', 'after:debut_at'],
            'statut' => ['nullable', 'string', 'max:32'],
        ]);

        $autorise = $request->user()->prestataires()->whereKey($payload['prestataire_id'])->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Ce prestataire ne vous appartient pas.'], 403);
        }

        $promotion = Promotion::create($payload);
        return response()->json($promotion, 201);
    }

    public function update(Request $request, Promotion $promotion): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($promotion->prestataire_id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Promotion introuvable.'], 404);
        }

        $payload = $request->validate([
            'libelle' => ['sometimes', 'string', 'max:255'],
            'type_remise' => ['sometimes', 'string', 'max:32'],
            'valeur' => ['sometimes', 'numeric', 'min:0'],
            'montant_minimum_commande' => ['nullable', 'numeric', 'min:0'],
            'reduction_plafond' => ['nullable', 'numeric', 'min:0'],
            'utilisations_max' => ['nullable', 'integer', 'min:1'],
            'debut_at' => ['sometimes', 'date'],
            'fin_at' => ['sometimes', 'date'],
            'statut' => ['sometimes', 'string', 'max:32'],
        ]);

        $promotion->update($payload);
        return response()->json($promotion->fresh());
    }
}
