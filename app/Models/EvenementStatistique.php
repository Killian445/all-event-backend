<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evenement analytique centralise.
 * Sert au dashboard, exports et analyses comportementales.
 */
class EvenementStatistique extends Model
{
    protected $table = 'evenements_statistiques';

    protected $fillable = [
        'type_evenement',
        'user_id',
        'session_id',
        'activite_id',
        'ville_id',
        'prestataire_id',
        'reservation_id',
        'campagne_publicitaire_id',
        'payload',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
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

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function campagnePublicitaire(): BelongsTo
    {
        return $this->belongsTo(CampagnePublicitaire::class);
    }
}
