<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Statut de votre compte prestataire</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f8fafc;color:#0f172a;padding:24px;">
  <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
    <h2 style="margin-top:0;">Mise à jour de votre compte prestataire</h2>
    <p>Compte concerné: <strong>{{ $prestataire->nom }}</strong></p>
    @if($statut === 'valide')
      <p style="color:#166534;font-weight:700;">Bonne nouvelle : votre compte a été validé.</p>
      <p>Bienvenue sur ALL EVENT. Votre espace prestataire est maintenant activé.</p>
      <p>Vous pouvez vous connecter, publier vos activités et gagner en visibilité auprès des clients.</p>
    @elseif($statut === 'rejete')
      <p style="color:#991b1b;font-weight:700;">Votre compte a été rejeté pour le moment.</p>
      <p>Merci de vérifier les informations fournies, puis de soumettre à nouveau votre dossier.</p>
      @if(!empty($prestataire->motif_rejet))
        <p><strong>Motif communiqué par l'administration:</strong></p>
        <p style="background:#fff7ed;border:1px solid #fed7aa;padding:10px;border-radius:8px;">{{ $prestataire->motif_rejet }}</p>
      @endif
    @else
      <p>Le statut de votre compte est désormais: <strong>{{ $statut }}</strong>.</p>
    @endif
  </div>
</body>
</html>

