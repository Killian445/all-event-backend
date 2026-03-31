<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Promotion/reduction applicable au checkout.
 * Peut cibler un prestataire entier ou une activite precise.
 */
class Promotion extends Model
{
    protected $table = 'promotions';

    protected $fillable = [
        'code',
        'libelle',
        'prestataire_id',
        'activite_id',
        'type_remise',
        'valeur',
        'montant_minimum_commande',
        'reduction_plafond',
        'utilisations_max',
        'utilisations_actuelles',
        'debut_at',
        'fin_at',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'valeur' => 'decimal:2',
            'montant_minimum_commande' => 'decimal:2',
            'reduction_plafond' => 'decimal:2',
            'debut_at' => 'datetime',
            'fin_at' => 'datetime',
        ];
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
