<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Litige;
use App\Models\MessageLitige;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pilotage admin des litiges.
 * Endpoints: ouverture, suivi, messages et cloture de dossiers.
 */
class GestionLitigeAdminController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'client_id' => ['required', 'integer', 'exists:users,id'],
            'prestataire_id' => ['required', 'integer', 'exists:prestataires,id'],
            'sujet' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'priorite' => ['nullable', 'string', 'in:faible,normale,haute'],
        ]);

        $litige = Litige::create([
            ...$payload,
            'admin_id' => $request->user()->id,
            'statut' => 'ouvert',
            'priorite' => $payload['priorite'] ?? 'normale',
        ]);

        MessageLitige::create([
            'litige_id' => $litige->id,
            'auteur_id' => $request->user()->id,
            'message' => $payload['description'],
            'interne_admin' => false,
        ]);

        return response()->json($litige, 201);
    }
    public function index(Request $request): JsonResponse
    {
        $query = Litige::query()->with(['client:id,name,email', 'prestataire:id,nom', 'reservation:id'])->latest();
        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }
        return response()->json($query->paginate(30));
    }

    public function show(Litige $litige): JsonResponse
    {
        return response()->json($litige->load(['messages.auteur:id,name,role', 'client:id,name,email', 'prestataire:id,nom', 'reservation:id']));
    }

    public function update(Request $request, Litige $litige): JsonResponse
    {
        $payload = $request->validate([
            'statut' => ['sometimes', 'string', 'in:ouvert,en_cours,ferme'],
            'priorite' => ['sometimes', 'string', 'in:faible,normale,haute'],
            'resolution' => ['nullable', 'string', 'max:20000'],
        ]);

        if (isset($payload['statut']) && $payload['statut'] === 'ferme') {
            $payload['ferme_le'] = now();
        }
        if (! $litige->admin_id) {
            $payload['admin_id'] = $request->user()->id;
        }

        $litige->update($payload);
        return response()->json($litige->fresh());
    }

    public function ajouterMessageInterne(Request $request, Litige $litige): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $message = MessageLitige::create([
            'litige_id' => $litige->id,
            'auteur_id' => $request->user()->id,
            'message' => $payload['message'],
            'interne_admin' => true,
        ]);

        return response()->json($message, 201);
    }

    public function ajouterMessage(Request $request, Litige $litige): JsonResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $message = MessageLitige::create([
            'litige_id' => $litige->id,
            'auteur_id' => $request->user()->id,
            'message' => $payload['message'],
            'interne_admin' => false,
        ]);

        return response()->json($message, 201);
    }
}
