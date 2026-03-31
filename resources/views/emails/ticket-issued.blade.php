<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Votre billet électronique</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f8fafc;color:#0f172a;padding:24px;">
  @php
    $ligne = $reservation->lignes->first();
    $creneau = $ligne?->creneau;
    $activite = $creneau?->activite;
    $lieu = $activite?->lieu;
  @endphp
  <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
    <h2 style="margin-top:0;">Votre billet électronique ALL EVENT</h2>
    <p><strong>Réservation #{{ $reservation->id }}</strong></p>
    <p>Activité: <strong>{{ $activite?->titre ?? 'Activité' }}</strong></p>
    <p>Date/heure: {{ optional($creneau?->debut_at)->format('d/m/Y H:i') ?? '—' }}</p>
    <p>Montant payé: <strong>{{ number_format((float)($reservation->paiement?->montant ?? $reservation->montant_total ?? 0), 0, ',', ' ') }} {{ $reservation->devise ?? 'MAD' }}</strong></p>
    <p>Code billet: <strong>{{ $reservation->billet?->code_public ?? '—' }}</strong></p>
    @if($lieu?->nom || $lieu?->adresse)
      <p>Lieu: {{ $lieu?->nom }} {{ $lieu?->adresse ? '- '.$lieu->adresse : '' }}</p>
    @endif
    @if(!empty($mapsUrl))
      <p><a href="{{ $mapsUrl }}" target="_blank" rel="noreferrer">Ouvrir le lieu dans Google Maps</a></p>
    @endif
    <p style="color:#64748b;font-size:13px;">Présentez ce billet à l'entrée de l'activité.</p>
  </div>
</body>
</html>

