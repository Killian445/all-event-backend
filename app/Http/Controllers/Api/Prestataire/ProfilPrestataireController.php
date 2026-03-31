<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Mail\PrestataireUnderReviewMail;
use App\Models\Prestataire;
use App\Models\PrestataireMembre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Gestion du profil organisation prestataire.
 * Endpoints: creation, edition, soumission validation et suivi statut.
 */
class ProfilPrestataireController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $prestataires = $request->user()->prestataires()->latest()->paginate(20);
        return response()->json($prestataires);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'raison_sociale' => ['nullable', 'string', 'max:255'],
            'numero_fiscal' => ['nullable', 'string', 'max:255'],
        ]);

        $prestataire = Prestataire::create(array_merge($payload, [
            'statut' => 'en_attente_validation',
            'valide_le' => null,
        ]));

        PrestataireMembre::create([
            'user_id' => $request->user()->id,
            'prestataire_id' => $prestataire->id,
            'role_membre' => 'owner',
            'rejoint_le' => now(),
        ]);

        if ($request->user()->role !== 'admin') {
            $request->user()->update(['role' => 'prestataire']);
        }

        return response()->json($prestataire, 201);
    }

    public function update(Request $request, Prestataire $prestataire): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($prestataire->id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Prestataire introuvable.'], 404);
        }

        $payload = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'raison_sociale' => ['nullable', 'string', 'max:255'],
            'numero_fiscal' => ['nullable', 'string', 'max:255'],
        ]);

        $prestataire->update(array_merge($payload, ['motif_rejet' => null]));

        return response()->json($prestataire->fresh());
    }

    public function soumettreValidation(Request $request, Prestataire $prestataire): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($prestataire->id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Prestataire introuvable.'], 404);
        }

        $documentsCount = $prestataire->documents()->count();
        $errors = [];
        if (! $prestataire->nom || trim((string) $prestataire->nom) === '') {
            $errors['nom'] = ['Le nom de la structure est requis.'];
        }
        if (! $prestataire->raison_sociale || trim((string) $prestataire->raison_sociale) === '') {
            $errors['raison_sociale'] = ['La raison sociale est requise avant soumission.'];
        }
        if (! $prestataire->numero_fiscal || trim((string) $prestataire->numero_fiscal) === '') {
            $errors['numero_fiscal'] = ['Le numero fiscal est requis avant soumission.'];
        }
        if ($documentsCount < 1) {
            $errors['documents'] = ['Au moins une piece de verification est requise avant soumission.'];
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $prestataire->update(['statut' => 'en_attente_validation', 'valide_le' => null, 'motif_rejet' => null]);
        foreach ($prestataire->users()->pluck('email')->filter()->unique() as $email) {
            Mail::to($email)->send(new PrestataireUnderReviewMail($prestataire));
        }
        return response()->json(['message' => 'Profil soumis pour validation.', 'prestataire' => $prestataire->fresh()]);
    }

    public function statut(Request $request, Prestataire $prestataire): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($prestataire->id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Prestataire introuvable.'], 404);
        }

        return response()->json([
            'id' => $prestataire->id,
            'statut' => $prestataire->statut,
            'valide_le' => $prestataire->valide_le,
        ]);
    }
}
