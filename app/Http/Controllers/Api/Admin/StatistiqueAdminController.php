<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampagnePublicitaire;
use App\Models\Categorie;
use App\Models\Commission;
use App\Models\EvenementStatistique;
use App\Models\Litige;
use App\Models\Paiement;
use App\Models\Prestataire;
use App\Models\Remboursement;
use App\Models\Reservation;
use App\Models\SignalementAvis;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Supervision admin globale.
 * Endpoints: dashboard KPI et export des indicateurs plateforme.
 */
class StatistiqueAdminController extends Controller
{
    private function parseDate(Request $request, string $key): ?Carbon
    {
        $raw = $request->query($key);
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }
        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param Builder<Reservation> $query */
    private function applyReservationFilters(Builder $query, Request $request): Builder
    {
        $from = $this->parseDate($request, 'from');
        $to = $this->parseDate($request, 'to');
        if ($from) {
            $query->where('created_at', '>=', $from->copy()->startOfDay());
        }
        if ($to) {
            $query->where('created_at', '<=', $to->copy()->endOfDay());
        }

        $villeId = $request->integer('ville_id');
        if ($villeId > 0) {
            $query->whereHas('lignes.creneau.activite', fn (Builder $q) => $q->where('ville_id', $villeId));
        }
        $categorieId = $request->integer('categorie_id');
        if ($categorieId > 0) {
            $query->whereHas('lignes.creneau.activite', fn (Builder $q) => $q->where('categorie_id', $categorieId));
        }

        return $query;
    }

    /** @param Builder<EvenementStatistique> $query */
    private function applyEventFilters(Builder $query, Request $request): Builder
    {
        $from = $this->parseDate($request, 'from');
        $to = $this->parseDate($request, 'to');
        if ($from) {
            $query->where('occurred_at', '>=', $from->copy()->startOfDay());
        }
        if ($to) {
            $query->where('occurred_at', '<=', $to->copy()->endOfDay());
        }

        $villeId = $request->integer('ville_id');
        if ($villeId > 0) {
            $query->where('ville_id', $villeId);
        }
        $categorieId = $request->integer('categorie_id');
        if ($categorieId > 0) {
            $query->whereHas('activite', fn (Builder $q) => $q->where('categorie_id', $categorieId));
        }
        return $query;
    }

    /** @return array<string, float|int> */
    private function buildDashboardKpis(): array
    {
        return [
            'utilisateurs_total' => User::query()->count(),
            'prestataires_en_attente' => Prestataire::query()
                ->where('statut', 'en_attente_validation')
                ->count(),
            'signalements_en_attente' => SignalementAvis::query()
                ->where('statut', 'en_attente')
                ->count(),
            'litiges_actifs' => Litige::query()
                ->whereIn('statut', ['ouvert', 'en_cours'])
                ->count(),
            'reservations_total' => Reservation::query()->count(),
            'reservations_payees' => Reservation::query()->where('statut', 'payee')->count(),
            'chiffre_affaires_total' => (float) Paiement::query()->whereIn('statut', ['reussi', 'paye'])->sum('montant'),
            'campagnes_validees' => CampagnePublicitaire::query()->where('statut', 'validee')->count(),
            'evenements_stats' => EvenementStatistique::query()->count(),
        ];
    }

    public function dashboard(): JsonResponse
    {
        return response()->json($this->buildDashboardKpis());
    }

    public function executive(Request $request): JsonResponse
    {
        $reservations = $this->applyReservationFilters(Reservation::query(), $request);
        $reservationsTotal = (clone $reservations)->count();
        $reservationsPayees = (clone $reservations)->where('statut', 'payee')->count();
        $conversion = $reservationsTotal > 0 ? round(($reservationsPayees / $reservationsTotal) * 100, 2) : 0;

        $paiements = Paiement::query()->whereIn('statut', ['reussi', 'paye']);
        if ($from = $this->parseDate($request, 'from')) {
            $paiements->where('created_at', '>=', $from->copy()->startOfDay());
        }
        if ($to = $this->parseDate($request, 'to')) {
            $paiements->where('created_at', '<=', $to->copy()->endOfDay());
        }

        return response()->json([
            'kpis' => [
                'utilisateurs_total' => User::query()->count(),
                'prestataires_total' => Prestataire::query()->count(),
                'reservations_total' => $reservationsTotal,
                'reservations_payees' => $reservationsPayees,
                'taux_conversion_payee_pct' => $conversion,
                'chiffre_affaires_total' => (float) (clone $paiements)->sum('montant'),
                'panier_moyen' => (float) ((clone $paiements)->avg('montant') ?? 0),
            ],
        ]);
    }

    public function marketplace(Request $request): JsonResponse
    {
        $reservations = $this->applyReservationFilters(Reservation::query(), $request);
        $paiementsPayes = Paiement::query()->whereIn('statut', ['reussi', 'paye']);
        if ($from = $this->parseDate($request, 'from')) {
            $paiementsPayes->where('created_at', '>=', $from->copy()->startOfDay());
        }
        if ($to = $this->parseDate($request, 'to')) {
            $paiementsPayes->where('created_at', '<=', $to->copy()->endOfDay());
        }

        $commissions = Commission::query();
        if ($from = $this->parseDate($request, 'from')) {
            $commissions->where('created_at', '>=', $from->copy()->startOfDay());
        }
        if ($to = $this->parseDate($request, 'to')) {
            $commissions->where('created_at', '<=', $to->copy()->endOfDay());
        }

        return response()->json([
            'kpis' => [
                'reservations_total' => (clone $reservations)->count(),
                'reservations_payees' => (clone $reservations)->where('statut', 'payee')->count(),
                'ca_encaisse' => (float) (clone $paiementsPayes)->sum('montant'),
                'commission_plateforme' => (float) (clone $commissions)->sum('montant_plateforme'),
                'net_prestataires' => (float) (clone $commissions)->sum('montant_net_prestataire'),
            ],
        ]);
    }

    public function demand(Request $request): JsonResponse
    {
        $events = $this->applyEventFilters(EvenementStatistique::query(), $request);

        $topVilles = (clone $events)
            ->selectRaw('ville_id, count(*) as total')
            ->whereNotNull('ville_id')
            ->with('ville:id,nom')
            ->groupBy('ville_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn (EvenementStatistique $row) => [
                'ville_id' => $row->ville_id,
                'ville' => $row->ville?->nom ?? '—',
                'total' => (int) ($row->total ?? 0),
            ])->values();

        $topActivites = (clone $events)
            ->selectRaw('activite_id, count(*) as total')
            ->whereNotNull('activite_id')
            ->with('activite:id,titre,categorie_id')
            ->groupBy('activite_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn (EvenementStatistique $row) => [
                'activite_id' => $row->activite_id,
                'activite' => $row->activite?->titre ?? '—',
                'total' => (int) ($row->total ?? 0),
            ])->values();

        return response()->json([
            'kpis' => [
                'evenements_total' => (clone $events)->count(),
                'sessions_uniques' => (int) (clone $events)->whereNotNull('session_id')->distinct('session_id')->count('session_id'),
                'utilisateurs_uniques' => (int) (clone $events)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            ],
            'top_villes' => $topVilles,
            'top_activites' => $topActivites,
        ]);
    }

    public function risk(Request $request): JsonResponse
    {
        $litiges = Litige::query();
        $signalements = SignalementAvis::query();
        $remboursements = Remboursement::query();

        if ($from = $this->parseDate($request, 'from')) {
            $litiges->where('created_at', '>=', $from->copy()->startOfDay());
            $signalements->where('created_at', '>=', $from->copy()->startOfDay());
            $remboursements->where('created_at', '>=', $from->copy()->startOfDay());
        }
        if ($to = $this->parseDate($request, 'to')) {
            $litiges->where('created_at', '<=', $to->copy()->endOfDay());
            $signalements->where('created_at', '<=', $to->copy()->endOfDay());
            $remboursements->where('created_at', '<=', $to->copy()->endOfDay());
        }

        return response()->json([
            'kpis' => [
                'litiges_ouverts' => (clone $litiges)->where('statut', 'ouvert')->count(),
                'litiges_en_cours' => (clone $litiges)->where('statut', 'en_cours')->count(),
                'litiges_fermes' => (clone $litiges)->where('statut', 'ferme')->count(),
                'signalements_en_attente' => (clone $signalements)->where('statut', 'en_attente')->count(),
                'remboursements_en_attente' => (clone $remboursements)->where('statut', 'en_attente')->count(),
                'remboursements_acceptes' => (clone $remboursements)->where('statut', 'accepte')->count(),
            ],
        ]);
    }

    public function dataProducts(Request $request): JsonResponse
    {
        $events = $this->applyEventFilters(EvenementStatistique::query(), $request);
        $reservations = $this->applyReservationFilters(Reservation::query(), $request);

        // Agrégations en PHP pour rester compatibles (MySQL / SQLite) sans SQL vendor-specific.
        $eventRows = (clone $events)
            ->with('activite:id,categorie_id')
            ->get(['id', 'occurred_at', 'activite_id']);

        $monthlyDemand = $eventRows
            ->groupBy(function (EvenementStatistique $row): string {
                return optional($row->occurred_at)->format('Y-m') ?? '—';
            })
            ->map(fn ($bucket, $periode) => ['periode' => $periode, 'volume' => $bucket->count()])
            ->sortBy('periode')
            ->values();

        $minimumGroupSize = 30;
        $topCategories = $eventRows
            ->filter(fn (EvenementStatistique $row): bool => (int) ($row->activite?->categorie_id ?? 0) > 0)
            ->groupBy(fn (EvenementStatistique $row): int => (int) $row->activite->categorie_id)
            ->map(fn ($bucket, $categorieId) => ['categorie_id' => (int) $categorieId, 'total' => $bucket->count()])
            ->filter(fn (array $item): bool => (int) $item['total'] >= $minimumGroupSize)
            ->sortByDesc('total')
            ->values()
            ->take(10)
            ->values();

        $categorieNames = Categorie::query()
            ->whereIn('id', $topCategories->pluck('categorie_id')->all())
            ->pluck('nom', 'id');
        $topCategories = $topCategories
            ->map(fn (array $item) => [
                ...$item,
                'categorie' => $categorieNames[$item['categorie_id']] ?? "Catégorie #{$item['categorie_id']}",
            ])
            ->values();

        return response()->json([
            'meta' => [
                'anonymized' => true,
                'minimum_group_size_hint' => $minimumGroupSize,
            ],
            'kpis' => [
                'demand_events_total' => (clone $events)->count(),
                'reservations_total' => (clone $reservations)->count(),
                'reservations_payees' => (clone $reservations)->where('statut', 'payee')->count(),
            ],
            'monthly_demand' => $monthlyDemand,
            'top_categories' => $topCategories,
        ]);
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $kpis = $this->buildDashboardKpis();

        return response()->streamDownload(function () use ($kpis): void {
            echo "metrique,valeur\n";
            foreach ($kpis as $k => $v) {
                echo $k.','.$v."\n";
            }
        }, 'rapport-admin.csv', ['Content-Type' => 'text/csv']);
    }
}
