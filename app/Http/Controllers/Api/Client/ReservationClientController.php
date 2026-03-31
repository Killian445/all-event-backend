<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Mail\TicketIssuedMail;
use App\Models\Commission;
use App\Models\JournalNotification;
use App\Models\Panier;
use App\Models\Remboursement;
use App\Models\RegleCommission;
use App\Models\Reservation;
use App\Services\Reservation\CheckoutPanierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Flux reservation client.
 * Endpoints: validation panier, paiement simule, billet, annulation, remboursement.
 */
class ReservationClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->user()
            ->reservations()
            ->with(['lignes.creneau.activite.prestataire', 'lignes.creneau.activite.lieu', 'paiement', 'billet', 'promotion'])
            ->latest()
            ->paginate(20);

        return response()->json($data);
    }

    public function show(Request $request, Reservation $reservation): JsonResponse
    {
        $this->autoriserReservation($request, $reservation);

        $reservation->load(['lignes.creneau.activite.prestataire', 'lignes.creneau.activite.lieu', 'paiement', 'billet', 'promotion']);

        return response()->json($reservation);
    }

    /**
     * Valide le panier actif : crée réservation, lignes, paiement en attente, billet.
     */
    public function validerPanier(Request $request, CheckoutPanierService $checkout): JsonResponse
    {
        $request->validate([
            'promotion_id' => ['nullable', 'integer', 'exists:promotions,id'],
        ]);

        $panier = Panier::query()
            ->where('user_id', $request->user()->id)
            ->where('statut', 'actif')
            ->first();

        if (! $panier) {
            return response()->json(['message' => 'Aucun panier actif.'], 404);
        }

        try {
            $resultat = $checkout->executer(
                $request->user(),
                $panier,
                $request->filled('promotion_id') ? (int) $request->input('promotion_id') : null
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Reservation creee. Paiement en attente (Stripe a brancher).',
            'reservation' => $resultat['reservation']->load(['lignes.creneau.activite', 'promotion']),
            'paiement' => $resultat['paiement'],
            'billet' => $resultat['billet'],
        ], 201);
    }

    public function billet(Request $request, Reservation $reservation): JsonResponse
    {
        $this->autoriserReservation($request, $reservation);

        $billet = $reservation->billet;
        if (! $billet) {
            return response()->json(['message' => 'Billet non genere.'], 404);
        }

        return response()->json($billet);
    }

    public function simulerPaiement(Request $request, Reservation $reservation): JsonResponse
    {
        $this->autoriserReservation($request, $reservation);

        $paiement = $reservation->paiement;
        if (! $paiement) {
            return response()->json(['message' => 'Paiement introuvable.'], 404);
        }

        $paiement->update([
            'statut' => 'reussi',
            'paye_le' => now(),
        ]);

        $reservation->update(['statut' => 'payee']);
        $reservation->load(['user:id,email,name', 'lignes.creneau.activite.lieu', 'billet', 'paiement']);

        $prestataireId = $reservation->lignes()
            ->with('creneau.activite:id,prestataire_id')
            ->get()
            ->pluck('creneau.activite.prestataire_id')
            ->filter()
            ->first();

        if ($prestataireId && ! $paiement->commission) {
            $taux = (float) (RegleCommission::query()
                ->where('prestataire_id', $prestataireId)
                ->whereDate('debut_effet', '<=', now())
                ->where(function ($q): void {
                    $q->whereNull('fin_effet')->orWhereDate('fin_effet', '>=', now());
                })
                ->latest('debut_effet')
                ->value('taux_pourcent') ?? 0);

            $montant = (float) $paiement->montant;
            $fee = round(($montant * $taux) / 100, 2);

            Commission::create([
                'paiement_id' => $paiement->id,
                'prestataire_id' => $prestataireId,
                'montant_plateforme' => $fee,
                'montant_net_prestataire' => round($montant - $fee, 2),
                'devise' => $paiement->devise,
            ]);
        }

        JournalNotification::create([
            'user_id' => $request->user()->id,
            'canal' => 'email',
            'cle_modele' => 'reservation_paiement_valide',
            'payload' => [
                'reservation_id' => $reservation->id,
                'montant' => $paiement->montant,
            ],
            'statut' => 'envoye',
            'envoye_le' => now(),
        ]);

        $firstLigne = $reservation->lignes->first();
        $lieu = $firstLigne?->creneau?->activite?->lieu;
        $mapsUrl = null;
        if ($lieu?->latitude !== null && $lieu?->longitude !== null) {
            $mapsUrl = 'https://www.google.com/maps?q='.$lieu->latitude.','.$lieu->longitude;
        } elseif ($lieu?->adresse || $lieu?->nom) {
            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='.urlencode(trim(($lieu->nom ?? '').' '.($lieu->adresse ?? '')));
        }

        if ($reservation->user?->email) {
            Mail::to($reservation->user->email)->send(new TicketIssuedMail($reservation, $mapsUrl));
        }

        return response()->json([
            'message' => 'Paiement simule et valide.',
            'reservation' => $reservation->fresh()->load('paiement'),
        ]);
    }

    public function annuler(Request $request, Reservation $reservation): JsonResponse
    {
        $this->autoriserReservation($request, $reservation);

        if (in_array($reservation->statut, ['annulee', 'remboursee'], true)) {
            return response()->json(['message' => 'Reservation deja annulee/remboursee.'], 422);
        }

        $reservation->update(['statut' => 'annulee']);

        JournalNotification::create([
            'user_id' => $request->user()->id,
            'canal' => 'email',
            'cle_modele' => 'reservation_annulee',
            'payload' => ['reservation_id' => $reservation->id],
            'statut' => 'envoye',
            'envoye_le' => now(),
        ]);

        return response()->json(['message' => 'Reservation annulee.', 'reservation' => $reservation->fresh()]);
    }

    public function demanderRemboursement(Request $request, Reservation $reservation): JsonResponse
    {
        $this->autoriserReservation($request, $reservation);

        $payload = $request->validate([
            'motif' => ['nullable', 'string', 'max:5000'],
        ]);

        $paiement = $reservation->paiement;
        if (! $paiement || ! in_array($paiement->statut, ['reussi', 'paye'], true)) {
            return response()->json(['message' => 'Paiement non eligible au remboursement.'], 422);
        }

        $rb = Remboursement::query()->create([
            'paiement_id' => $paiement->id,
            'reservation_id' => $reservation->id,
            'demandeur_id' => $request->user()->id,
            'montant' => $paiement->montant,
            'statut' => 'demande',
            'motif' => $payload['motif'] ?? null,
        ]);

        return response()->json($rb, 201);
    }

    private function autoriserReservation(Request $request, Reservation $reservation): void
    {
        if ($reservation->user_id !== $request->user()->id) {
            abort(404);
        }
    }
}
