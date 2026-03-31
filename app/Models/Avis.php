<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Avis client sur une activite reservee.
 * Supporte moderation et reponse prestataire.
 */
class Avis extends Model
{
    protected $table = 'avis';

    protected $fillable = [
        'user_id',
        'activite_id',
        'reservation_id',
        'note',
        'commentaire',
        'reponse_prestataire',
        'repondu_le',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'repondu_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function signalements(): HasMany
    {
        return $this->hasMany(SignalementAvis::class);
    }

    public function estModere(): bool
    {
        return in_array($this->statut, ['visible', 'masque'], true);
    }
}
