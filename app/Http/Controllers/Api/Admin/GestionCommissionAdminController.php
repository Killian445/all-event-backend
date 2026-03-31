<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Remboursement;
use App\Models\RegleCommission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion admin des commissions et remboursements.
 * Endpoints: regles, suivi commissions et traitement remboursements.
 */
class GestionCommissionAdminController extends Controller
{
    public function regles(Request $request): JsonResponse
    {
        $query = RegleCommission::query()->with('prestataire:id,nom')->latest();
        if ($request->filled('prestataire_id')) {
            $query->where('prestataire_id', $request->integer('prestataire_id'));
        }
        return response()->json($query->paginate(30));
    }

    public function storeRegle(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'prestataire_id' => ['required', 'integer', 'exists:prestataires,id'],
            'taux_pourcent' => ['required', 'numeric', 'min:0', 'max:100'],
            'debut_effet' => ['required', 'date'],
            'fin_effet' => ['nullable', 'date', 'after_or_equal:debut_effet'],
        ]);

        $regle = RegleCommission::create($payload);
        return response()->json($regle, 201);
    }

    public function updateRegle(Request $request, RegleCommission $regleCommission): JsonResponse
    {
        $payload = $request->validate([
            'taux_pourcent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'debut_effet' => ['sometimes', 'date'],
            'fin_effet' => ['nullable', 'date'],
        ]);

        $regleCommission->update($payload);
        return response()->json($regleCommission->fresh());
    }

    public function commissions(Request $request): JsonResponse
    {
        $query = Commission::query()->with(['prestataire:id,nom', 'paiement:id,reservation_id,montant,statut'])->latest();
        if ($request->filled('prestataire_id')) {
            $query->where('prestataire_id', $request->integer('prestataire_id'));
        }
        return response()->json($query->paginate(30));
    }

    public function remboursements(): JsonResponse
    {
        return response()->json(
            Remboursement::query()
                ->with(['reservation:id,statut', 'paiement:id,montant,statut', 'demandeur:id,name,email'])
                ->latest()
                ->paginate(30)
        );
    }

    public function traiterRemboursement(Request $request, Remboursement $remboursement): JsonResponse
    {
        $payload = $request->validate([
            'statut' => ['required', 'string', 'in:accepte,refuse'],
        ]);

        $remboursement->update([
            'statut' => $payload['statut'],
            'traite_le' => now(),
        ]);

        if ($payload['statut'] === 'accepte') {
            $remboursement->paiement?->update(['statut' => 'rembourse']);
            $remboursement->reservation?->update(['statut' => 'remboursee']);
        }

        return response()->json($remboursement->fresh());
    }
}
