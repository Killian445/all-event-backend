<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Activation de compte</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f8fafc;color:#0f172a;padding:24px;">
  <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
    <h2 style="margin-top:0;">Activation de votre compte ALL EVENT</h2>
    <p>Bonjour {{ $user->name }},</p>
    <p>Utilisez ce code OTP pour activer votre compte :</p>
    <p style="font-size:28px;font-weight:800;letter-spacing:4px;margin:16px 0;color:#1d4ed8;">{{ $otpCode }}</p>
    <p>Ce code expire dans 10 minutes.</p>
    <p style="color:#64748b;font-size:13px;">Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
  </div>
</body>
</html>

