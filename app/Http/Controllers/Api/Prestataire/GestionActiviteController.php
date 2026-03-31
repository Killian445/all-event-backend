<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Creneau;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestion des activites et creneaux cote prestataire.
 * Endpoints CRUD activites + CRUD disponibilites.
 */
class GestionActiviteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');

        $activites = Activite::query()
            ->whereIn('prestataire_id', $prestataireIds)
            ->with(['categorie:id,nom', 'ville:id,nom', 'lieu:id,nom', 'medias:id,activite_id,url,ordre'])
            ->withCount('creneaux')
            ->latest()
            ->paginate(20);

        return response()->json($activites);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'prestataire_id' => ['required', 'integer', 'exists:prestataires,id'],
            'categorie_id' => ['required', 'integer', 'exists:categories,id'],
            'ville_id' => ['required', 'integer', 'exists:villes,id'],
            'lieu_id' => ['nullable', 'integer', 'exists:lieux,id'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['nullable', 'string', 'in:brouillon,en_attente_validation'],
            'prix_base' => ['required', 'numeric', 'min:0'],
        ]);

        $autorise = $request->user()->prestataires()->whereKey($payload['prestataire_id'])->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Ce prestataire ne vous appartient pas.'], 403);
        }

        $payload['statut'] = in_array($payload['statut'] ?? 'brouillon', ['brouillon', 'en_attente_validation'], true)
            ? ($payload['statut'] ?? 'brouillon')
            : 'brouillon';
        $activite = Activite::create($payload);

        return response()->json($activite, 201);
    }

    public function show(Request $request, Activite $activite): JsonResponse
    {
        if (! $this->appartientAuPrestataire($request, $activite)) {
            return response()->json(['message' => 'Activite introuvable.'], 404);
        }

        $activite->load(['medias', 'creneaux', 'categorie', 'ville', 'lieu']);
        return response()->json($activite);
    }

    public function update(Request $request, Activite $activite): JsonResponse
    {
        if (! $this->appartientAuPrestataire($request, $activite)) {
            return response()->json(['message' => 'Activite introuvable.'], 404);
        }

        $payload = $request->validate([
            'categorie_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'ville_id' => ['sometimes', 'integer', 'exists:villes,id'],
            'lieu_id' => ['nullable', 'integer', 'exists:lieux,id'],
            'titre' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['sometimes', 'string', 'in:brouillon,en_attente_validation'],
            'prix_base' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $statutFromRequest = $payload['statut'] ?? null;
        unset($payload['statut']);

        $wasPubliee = $activite->statut === 'publiee';
        $contentFields = ['titre', 'description', 'prix_base', 'categorie_id', 'ville_id', 'lieu_id'];
        $contentChanged = count(array_intersect(array_keys($payload), $contentFields)) > 0;

        if ($payload !== []) {
            $activite->update($payload);
        }

        if ($statutFromRequest !== null) {
            $activite->update(['statut' => $statutFromRequest]);
        } elseif ($wasPubliee && $contentChanged) {
            $activite->update(['statut' => 'en_attente_validation']);
        }

        return response()->json($activite->fresh());
    }

    public function destroy(Request $request, Activite $activite): JsonResponse
    {
        if (! $this->appartientAuPrestataire($request, $activite)) {
            return response()->json(['message' => 'Activite introuvable.'], 404);
        }

        $activite->delete();
        return response()->json(['message' => 'Activite supprimee.']);
    }

    public function storeCreneau(Request $request, Activite $activite): JsonResponse
    {
        if (! $this->appartientAuPrestataire($request, $activite)) {
            return response()->json(['message' => 'Activite introuvable.'], 404);
        }

        $payload = $request->validate([
            'debut_at' => ['required', 'date'],
            'fin_at' => ['required', 'date', 'after:debut_at'],
            'capacite_totale' => ['required', 'integer', 'min:1'],
            'prix_applique' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['nullable', 'string', 'max:32'],
        ]);

        $payload['capacite_restante'] = $payload['capacite_totale'];
        $payload['statut'] = $payload['statut'] ?? 'ouvert';
        $creneau = $activite->creneaux()->create($payload);

        return response()->json($creneau, 201);
    }

    public function updateCreneau(Request $request, Activite $activite, Creneau $creneau): JsonResponse
    {
        if (! $this->appartientAuPrestataire($request, $activite) || $creneau->activite_id !== $activite->id) {
            return response()->json(['message' => 'Creneau introuvable.'], 404);
        }

        $payload = $request->validate([
            'debut_at' => ['sometimes', 'date'],
            'fin_at' => ['sometimes', 'date', 'after:debut_at'],
            'capacite_totale' => ['sometimes', 'integer', 'min:1'],
            'capacite_restante' => ['sometimes', 'integer', 'min:0'],
            'prix_applique' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['sometimes', 'string', 'max:32'],
        ]);

        $creneau->update($payload);
        return response()->json($creneau->fresh());
    }

    public function destroyCreneau(Request $request, Activite $activite, Creneau $creneau): JsonResponse
    {
        if (! $this->appartientAuPrestataire($request, $activite) || $creneau->activite_id !== $activite->id) {
            return response()->json(['message' => 'Creneau introuvable.'], 404);
        }

        $creneau->delete();
        return response()->json(['message' => 'Creneau supprime.']);
    }

    private function appartientAuPrestataire(Request $request, Activite $activite): bool
    {
        return $request->user()
            ->prestataires()
            ->whereKey($activite->prestataire_id)
            ->exists();
    }
}
