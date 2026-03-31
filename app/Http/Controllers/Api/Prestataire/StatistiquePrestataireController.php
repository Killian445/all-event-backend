<?php

namespace App\Http\Controllers\Api\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\EvenementStatistique;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Statistiques prestataire.
 * Endpoints: dashboard KPI et export synthetique.
 */
class StatistiquePrestataireController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');

        $reservations = Reservation::query()
            ->whereHas('lignes.creneau.activite', fn ($q) => $q->whereIn('prestataire_id', $prestataireIds));

        $avisVisibles = Avis::query()
            ->whereHas('activite', fn ($q) => $q->whereIn('prestataire_id', $prestataireIds))
            ->where('statut', 'visible');

        $avgNote = (clone $avisVisibles)->avg('note'); /** @var float|string|null $avgNote */

        $kpis = [
            'reservations_total' => (clone $reservations)->count(),
            'reservations_payees' => (clone $reservations)->where('statut', 'payee')->count(),
            'reservations_confirmees' => (clone $reservations)->where('statut', 'confirmee')->count(),
            'reservations_annulees' => (clone $reservations)->where('statut', 'annulee')->count(),
            'chiffre_affaires' => (float) (clone $reservations)->where('statut', 'payee')->sum('montant_total'),
            'evenements_stats' => EvenementStatistique::query()->whereIn('prestataire_id', $prestataireIds)->count(),
            'avis_total' => (clone $avisVisibles)->count(),
            'avis_note_moyenne' => round((float) ($avgNote ?? 0), 2),
        ];

        return response()->json($kpis);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $prestataireIds = $request->user()->prestataires()->pluck('prestataires.id');
        $reservations = Reservation::query()
            ->whereHas('lignes.creneau.activite', fn ($q) => $q->whereIn('prestataire_id', $prestataireIds));

        $avisVisibles = Avis::query()
            ->whereHas('activite', fn ($q) => $q->whereIn('prestataire_id', $prestataireIds))
            ->where('statut', 'visible');

        $avgNote = (clone $avisVisibles)->avg('note');

        $kpis = [
            'reservations_total' => (clone $reservations)->count(),
            'reservations_payees' => (clone $reservations)->where('statut', 'payee')->count(),
            'reservations_confirmees' => (clone $reservations)->where('statut', 'confirmee')->count(),
            'reservations_annulees' => (clone $reservations)->where('statut', 'annulee')->count(),
            'chiffre_affaires' => (float) (clone $reservations)->where('statut', 'payee')->sum('montant_total'),
            'evenements_stats' => EvenementStatistique::query()->whereIn('prestataire_id', $prestataireIds)->count(),
            'avis_total' => (clone $avisVisibles)->count(),
            'avis_note_moyenne' => round((float) ($avgNote ?? 0), 2),
        ];

        return response()->streamDownload(function () use ($kpis): void {
            echo "metrique,valeur\n";
            foreach ($kpis as $k => $v) {
                echo $k.','.$v."\n";
            }
        }, 'rapport-prestataire.csv', ['Content-Type' => 'text/csv']);
    }
}
