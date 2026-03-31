<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\CampagnePublicitaire;
use App\Models\JournalNotification;
use App\Models\PaiementPublicite;
use App\Services\Media\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des campagnes publicitaires prestataire.
 * Endpoints: CRUD campagne et paiement simule de mise en avant.
 */
class GestionCampagnePublicitaireController extends Controller
{
    public function __construct(
        private readonly CloudinaryService $cloudinary,
    ) {}
    /**
     * Enrichit une campagne avec les memes relations / agregats que l'index (pour reponses POST/PUT).
     */
    private function campagnePourReponse(CampagnePublicitaire $campagne): CampagnePublicitaire
    {
        $campagne->load(['ville:id,nom', 'categorie:id,nom', 'activite:id,titre']);
        $campagne->loadCount('evenementsStatistiques');
        $campagne->loadSum('paiements', 'montant');

        return $campagne;
    }

    public function index(Request $request): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');

        return response()->json(
            CampagnePublicitaire::query()
                ->whereIn('prestataire_id', $prestataireIds)
                ->with(['ville:id,nom', 'categorie:id,nom', 'activite:id,titre'])
                ->withCount('evenementsStatistiques')
                ->withSum('paiements', 'montant')
                ->latest()
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'prestataire_id' => ['required', 'integer', 'exists:prestataires,id'],
            'titre' => ['required', 'string', 'max:255'],
            'emplacement' => ['required', 'string', 'max:64'],
            'ville_id' => ['nullable', 'integer', 'exists:villes,id'],
            'categorie_id' => ['nullable', 'integer', 'exists:categories,id'],
            'activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'debut_at' => ['required', 'date'],
            'fin_at' => ['required', 'date', 'after:debut_at'],
            'priorite' => ['nullable', 'integer', 'min:0'],
            'budget_montant' => ['nullable', 'numeric', 'min:0'],
            'visuel' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('visuel')) {
            $upload = $this->cloudinary->uploadImage($request->file('visuel'), 'allevent/campagnes');
            $payload['image_url'] = $upload['secure_url'] ?? $upload['url'] ?? null;
        }

        $autorise = $request->user()->prestataires()->whereKey($payload['prestataire_id'])->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Ce prestataire ne vous appartient pas.'], 403);
        }

        unset($payload['visuel']);
        $payload['statut'] = 'en_attente_validation';
        $campagne = CampagnePublicitaire::create($payload);

        return response()->json($this->campagnePourReponse($campagne->fresh()), 201);
    }

    public function update(Request $request, CampagnePublicitaire $campagne): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($campagne->prestataire_id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Campagne introuvable.'], 404);
        }

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
            'visuel' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('visuel')) {
            $upload = $this->cloudinary->uploadImage($request->file('visuel'), 'allevent/campagnes');
            $payload['image_url'] = $upload['secure_url'] ?? $upload['url'] ?? null;
        }

        unset($payload['visuel']);

        // Toute modification repasse la campagne en attente de validation admin.
        $payload['statut'] = 'en_attente_validation';
        $campagne->update($payload);

        return response()->json($this->campagnePourReponse($campagne->fresh()));
    }

    public function simulerPaiement(Request $request, CampagnePublicitaire $campagne): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($campagne->prestataire_id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Campagne introuvable.'], 404);
        }

        $payload = $request->validate([
            'montant' => ['required', 'numeric', 'min:0'],
        ]);

        $paiement = PaiementPublicite::create([
            'campagne_publicitaire_id' => $campagne->id,
            'montant' => $payload['montant'],
            'devise' => 'MAD',
            'statut' => 'reussi',
            'fournisseur' => 'simulation',
            'paye_le' => now(),
        ]);

        JournalNotification::create([
            'user_id' => $request->user()->id,
            'canal' => 'email',
            'cle_modele' => 'campagne_paiement_valide',
            'payload' => ['campagne_id' => $campagne->id, 'montant' => $payload['montant']],
            'statut' => 'envoye',
            'envoye_le' => now(),
        ]);

        return response()->json([
            'message' => 'Paiement campagne valide (simulation).',
            'paiement' => $paiement,
            'campagne' => $this->campagnePourReponse($campagne->fresh()),
        ], 201);
    }

    public function destroy(Request $request, CampagnePublicitaire $campagne): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($campagne->prestataire_id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Campagne introuvable.'], 404);
        }

        $campagne->delete();
        return response()->json(['message' => 'Campagne supprimee.']);
    }
}
