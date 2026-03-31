<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Signalement d'avis effectue par un utilisateur.
 * Alimente la file de moderation admin.
 */
class SignalementAvis extends Model
{
    protected $table = 'signalements_avis';

    protected $fillable = [
        'avis_id',
        'user_id',
        'motif',
        'details',
        'statut',
    ];

    public function avis(): BelongsTo
    {
        return $this->belongsTo(Avis::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
