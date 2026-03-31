<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\PrestataireDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Depot des pieces de verification par le prestataire (PDF / images).
 */
class PrestataireDocumentController extends Controller
{
    public function index(Request $request, Prestataire $prestataire): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($prestataire->id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Prestataire introuvable.'], 404);
        }

        $documents = $prestataire->documents()
            ->with('uploadedBy:id,name')
            ->latest()
            ->get();

        return response()->json($documents);
    }

    public function store(Request $request, Prestataire $prestataire): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($prestataire->id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Prestataire introuvable.'], 404);
        }

        $payload = $request->validate([
            'fichier' => ['required', 'file', 'max:10240', 'mimetypes:application/pdf,image/jpeg,image/png'],
            'libelle' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('fichier');
        $path = $file->store("prestataire-documents/{$prestataire->id}", 'local');

        $document = PrestataireDocument::create([
            'prestataire_id' => $prestataire->id,
            'uploaded_by_user_id' => $request->user()->id,
            'libelle' => $payload['libelle'] ?? null,
            'nom_original' => $file->getClientOriginalName(),
            'chemin_disque' => $path,
            'mime_type' => $file->getClientMimeType(),
            'taille_octets' => $file->getSize(),
        ]);

        $document->load('uploadedBy:id,name');

        return response()->json($document, 201);
    }

    public function destroy(Request $request, Prestataire $prestataire, PrestataireDocument $document): JsonResponse
    {
        $autorise = $request->user()->prestataires()->whereKey($prestataire->id)->exists();
        if (! $autorise) {
            return response()->json(['message' => 'Prestataire introuvable.'], 404);
        }

        if ($document->prestataire_id !== $prestataire->id) {
            return response()->json(['message' => 'Document introuvable.'], 404);
        }

        Storage::disk('local')->delete($document->chemin_disque);
        $document->delete();

        return response()->json(['message' => 'Document supprime.']);
    }
}
