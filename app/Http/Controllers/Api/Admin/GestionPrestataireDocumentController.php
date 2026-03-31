<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\PrestataireDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Consultation et telechargement des pieces de verification par l'admin.
 */
class GestionPrestataireDocumentController extends Controller
{
    public function index(Prestataire $prestataire): JsonResponse
    {
        $documents = $prestataire->documents()
            ->with('uploadedBy:id,name,email')
            ->latest()
            ->get();

        return response()->json($documents);
    }

    public function telecharger(Prestataire $prestataire, PrestataireDocument $document): StreamedResponse|JsonResponse
    {
        if ($document->prestataire_id !== $prestataire->id) {
            return response()->json(['message' => 'Document introuvable.'], 404);
        }

        if (! Storage::disk('local')->exists($document->chemin_disque)) {
            return response()->json(['message' => 'Fichier introuvable sur le disque.'], 404);
        }

        return Storage::disk('local')->response(
            $document->chemin_disque,
            $document->nom_original,
            [
                'Content-Type' => $document->mime_type ?? 'application/octet-stream',
            ]
        );
    }
}
