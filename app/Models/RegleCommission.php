<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Regle de commission configurable par prestataire.
 * Permet la gestion temporelle des taux de prelevement.
 */
class RegleCommission extends Model
{
    protected $table = 'regles_commission';

    protected $fillable = [
        'prestataire_id',
        'taux_pourcent',
        'debut_effet',
        'fin_effet',
    ];

    protected function casts(): array
    {
        return [
            'taux_pourcent' => 'decimal:2',
            'debut_effet' => 'date',
            'fin_effet' => 'date',
        ];
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }
}
