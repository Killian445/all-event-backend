<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Litige;
use App\Models\MessageLitige;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des litiges cote client.
 * Endpoints: ouvrir un litige, consulter, et poster des messages.
 */
class LitigeClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $litiges = Litige::query()
            ->where('client_id', $request->user()->id)
            ->with(['reservation:id', 'prestataire:id,nom', 'admin:id,name'])
            ->latest()
            ->paginate(20);

        return response()->json($litiges);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'sujet' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'priorite' => ['nullable', 'string', 'in:faible,normale,haute'],
        ]);

        $reservation = Reservation::query()->with('lignes.creneau.activite')->findOrFail($payload['reservation_id']);
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Reservation introuvable.'], 404);
        }

        $firstLine = $reservation->lignes->first();
        $prestataireId = $firstLine?->creneau?->activite?->prestataire_id;
        if (! $prestataireId) {
            return response()->json(['message' => 'Impossible de rattacher le prestataire.'], 422);
        }

        $litige = Litige::create([
            'reservation_id' => $reservation->id,
            'client_id' => $request->user()->id,
            'prestataire_id' => $prestataireId,
            'sujet' => $payload['sujet'],
            'description' => $payload['description'],
            'priorite' => $payload['priorite'] ?? 'normale',
            'statut' => 'ouvert',
        ]);

        MessageLitige::create([
            'litige_id' => $litige->id,
            'auteur_id' => $request->user()->id,
            'message' => $payload['description'],
            'interne_admin' => false,
        ]);

        return response()->json($litige->load(['prestataire:id,nom']), 201);
    }

    public function show(Request $request, Litige $litige): JsonResponse
    {
        if ($litige->client_id !== $request->user()->id) {
            return response()->json(['message' => 'Litige introuvable.'], 404);
        }

        return response()->json($litige->load(['messages.auteur:id,name,role', 'prestataire:id,nom', 'admin:id,name']));
    }

    public function ajouterMessage(Request $request, Litige $litige): JsonResponse
    {
        if ($litige->client_id !== $request->user()->id) {
            return response()->json(['message' => 'Litige introuvable.'], 404);
        }

        if ($litige->statut === 'ferme') {
            return response()->json(['message' => 'Le litige est ferme.'], 422);
        }

        $payload = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $msg = MessageLitige::create([
            'litige_id' => $litige->id,
            'auteur_id' => $request->user()->id,
            'message' => $payload['message'],
            'interne_admin' => false,
        ]);

        return response()->json($msg, 201);
    }
}
