<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PrestataireDecisionMail;
use App\Models\JournalNotification;
use App\Models\Prestataire;
use App\Services\TransactionalMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Supervision admin des prestataires.
 * Endpoints: listing, fiche, validation ou rejet du statut metier.
 */
class GestionPrestataireController extends Controller
{
    public function __construct(
        private readonly TransactionalMailer $transactionalMailer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Prestataire::query()
            ->with('documents:id,prestataire_id')
            ->withCount(['activites', 'users', 'documents'])
            ->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        return response()->json($query->paginate(30));
    }

    public function show(Prestataire $prestataire): JsonResponse
    {
        $prestataire->loadCount('activites');
        $prestataire->load([
            'users:id,name,email',
            'documents.uploadedBy:id,name,email',
        ]);

        return response()->json($prestataire);
    }

    public function updateStatut(Request $request, Prestataire $prestataire): JsonResponse
    {
        $payload = $request->validate([
            'statut' => ['required', 'string', 'in:en_attente_validation,valide,rejete'],
            'motif_rejet' => ['nullable', 'string', 'max:5000'],
        ]);
        if ($payload['statut'] === 'rejete' && empty(trim((string) ($payload['motif_rejet'] ?? '')))) {
            return response()->json([
                'message' => 'Le motif de rejet est requis.',
                'errors' => ['motif_rejet' => ['Le motif de rejet est requis.']],
            ], 422);
        }

        $prestataire->update([
            'statut' => $payload['statut'],
            'valide_le' => $payload['statut'] === 'valide' ? now() : null,
            'motif_rejet' => $payload['statut'] === 'rejete'
                ? trim((string) ($payload['motif_rejet'] ?? ''))
                : null,
        ]);

        $prestataire->load('users');

        foreach ($prestataire->users as $user) {
            if ($payload['statut'] === 'valide') {
                $user->forceFill([
                    'role' => 'prestataire',
                    'status' => 'active',
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            } elseif ($payload['statut'] === 'rejete') {
                $user->forceFill(['status' => 'inactive'])->save();
            }
            $this->transactionalMailer->send($user->email, new PrestataireDecisionMail($prestataire, $payload['statut']));
            JournalNotification::create([
                'user_id' => $user->id,
                'canal' => 'email',
                'cle_modele' => 'prestataire_statut_mis_a_jour',
                'payload' => ['prestataire_id' => $prestataire->id, 'statut' => $payload['statut']],
                'statut' => 'envoye',
                'envoye_le' => now(),
            ]);
        }

        return response()->json($prestataire->fresh());
    }
}
