<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Reservation;
use App\Models\SignalementAvis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des avis cote client.
 * Endpoints: creation, edition, suppression et signalement d'avis.
 */
class AvisClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = Avis::query()
            ->where('user_id', $request->user()->id)
            ->with(['activite:id,titre', 'reservation:id,statut'])
            ->latest()
            ->paginate(30);

        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        $donnees = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'activite_id' => ['required', 'integer', 'exists:activites,id'],
            'note' => ['required', 'integer', 'min:1', 'max:5'],
            'commentaire' => ['nullable', 'string', 'max:5000'],
        ]);

        $reservation = Reservation::query()->findOrFail($donnees['reservation_id']);

        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Reservation introuvable.'], 404);
        }

        if ($reservation->statut !== 'payee') {
            return response()->json(['message' => 'La reservation doit etre payee pour laisser un avis.'], 422);
        }

        $ok = $reservation->lignes()
            ->whereHas('creneau', fn ($q) => $q->where('activite_id', $donnees['activite_id']))
            ->with('creneau')
            ->get();

        if ($ok->isEmpty()) {
            return response()->json(['message' => 'Activite incoherente avec la reservation.'], 422);
        }

        $finPlusTard = $ok->max(fn ($l) => $l->creneau->fin_at);
        if ($finPlusTard->isFuture()) {
            return response()->json(['message' => 'Lactivite doit etre terminee avant de noter.'], 422);
        }

        $avis = Avis::create([
            'user_id' => $request->user()->id,
            'activite_id' => $donnees['activite_id'],
            'reservation_id' => $reservation->id,
            'note' => $donnees['note'],
            'commentaire' => $donnees['commentaire'] ?? null,
            'statut' => 'visible',
        ]);

        return response()->json($avis->load('activite'), 201);
    }

    public function update(Request $request, Avis $avis): JsonResponse
    {
        if ($avis->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Avis introuvable.'], 404);
        }

        $payload = $request->validate([
            'note' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'commentaire' => ['nullable', 'string', 'max:5000'],
        ]);

        $avis->update($payload);
        return response()->json($avis->fresh()->load('activite'));
    }

    public function destroy(Request $request, Avis $avis): JsonResponse
    {
        if ($avis->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Avis introuvable.'], 404);
        }

        $avis->delete();
        return response()->json(['message' => 'Avis supprime.']);
    }

    public function signaler(Request $request, Avis $avis): JsonResponse
    {
        $payload = $request->validate([
            'motif' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:5000'],
        ]);

        $signalement = SignalementAvis::query()->updateOrCreate(
            [
                'avis_id' => $avis->id,
                'user_id' => $request->user()->id,
            ],
            [
                'motif' => $payload['motif'],
                'details' => $payload['details'] ?? null,
                'statut' => 'en_attente',
            ]
        );

        return response()->json($signalement, 201);
    }
}
