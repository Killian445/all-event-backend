<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Creneau;
use App\Models\LignePanier;
use App\Models\Panier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestion du panier client.
 * Endpoints: lire panier, ajouter/modifier/supprimer lignes, vider panier.
 */
class PanierClientController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $panier = $this->panierActif($request)->load(['lignes.creneau.activite']);

        return response()->json($panier);
    }

    public function ajouterLigne(Request $request): JsonResponse
    {
        $donnees = $request->validate([
            'creneau_id' => ['required', 'integer', 'exists:creneaux,id'],
            'quantite' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($request, $donnees) {
            $creneau = Creneau::query()
                ->whereKey($donnees['creneau_id'])
                ->lockForUpdate()
                ->with('activite')
                ->firstOrFail();

            if ($creneau->statut !== 'ouvert') {
                return response()->json(['message' => 'Ce creneau nest pas reservable.'], 422);
            }

            if ($creneau->activite->statut !== 'publiee') {
                return response()->json(['message' => 'Activite non disponible.'], 422);
            }

            if ($creneau->capacite_restante < $donnees['quantite']) {
                return response()->json(['message' => 'Pas assez de places disponibles.'], 422);
            }

            $prix = $this->prixUnitaire($creneau);
            $panier = $this->panierActif($request);

            $existant = LignePanier::query()
                ->where('panier_id', $panier->id)
                ->where('creneau_id', $creneau->id)
                ->first();

            if ($existant) {
                $nouvelleQty = $existant->quantite + $donnees['quantite'];
                if ($creneau->capacite_restante < $nouvelleQty) {
                    return response()->json(['message' => 'Pas assez de places pour cette quantite.'], 422);
                }
                $existant->update([
                    'quantite' => $nouvelleQty,
                    'prix_unitaire_snapshot' => $prix,
                ]);
            } else {
                LignePanier::create([
                    'panier_id' => $panier->id,
                    'creneau_id' => $creneau->id,
                    'quantite' => $donnees['quantite'],
                    'prix_unitaire_snapshot' => $prix,
                ]);
            }

            return response()->json($panier->fresh()->load(['lignes.creneau.activite']), 201);
        });
    }

    public function modifierLigne(Request $request, LignePanier $lignePanier): JsonResponse
    {
        $this->autoriserPanier($request, $lignePanier);

        $donnees = $request->validate([
            'quantite' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($request, $lignePanier, $donnees) {
            $creneau = Creneau::query()
                ->whereKey($lignePanier->creneau_id)
                ->lockForUpdate()
                ->with('activite')
                ->firstOrFail();

            if ($creneau->capacite_restante < $donnees['quantite']) {
                return response()->json(['message' => 'Pas assez de places disponibles.'], 422);
            }

            $prix = $this->prixUnitaire($creneau);
            $lignePanier->update([
                'quantite' => $donnees['quantite'],
                'prix_unitaire_snapshot' => $prix,
            ]);

            return response()->json($lignePanier->panier->fresh()->load(['lignes.creneau.activite']));
        });
    }

    public function supprimerLigne(Request $request, LignePanier $lignePanier): JsonResponse
    {
        $this->autoriserPanier($request, $lignePanier);

        $panier = $lignePanier->panier;
        $lignePanier->delete();

        return response()->json($panier->fresh()->load(['lignes.creneau.activite']));
    }

    public function vider(Request $request): JsonResponse
    {
        $panier = $this->panierActif($request);
        $panier->lignes()->delete();

        return response()->json($panier->fresh()->load(['lignes.creneau.activite']));
    }

    private function panierActif(Request $request): Panier
    {
        return Panier::query()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'statut' => 'actif',
            ],
            [
                'expire_le' => now()->addHours(2),
            ]
        );
    }

    private function autoriserPanier(Request $request, LignePanier $lignePanier): void
    {
        $panier = $lignePanier->panier;
        if ($panier->user_id !== $request->user()->id || $panier->statut !== 'actif') {
            abort(404);
        }
    }

    private function prixUnitaire(Creneau $creneau): string
    {
        if ($creneau->prix_applique !== null) {
            return number_format((float) $creneau->prix_applique, 2, '.', '');
        }

        return number_format((float) $creneau->activite->prix_base, 2, '.', '');
    }
}
