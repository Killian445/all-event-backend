<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lieu physique d'execution d'une activite.
 * Porte l'adresse et la geolocalisation pour l'affichage carte.
 */
class Lieu extends Model
{
    protected $table = 'lieux';

    // ville_id: rattachement geographique; latitude/longitude: position carte.
    protected $fillable = [
        'ville_id',
        'nom',
        'adresse',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class);
    }
}
