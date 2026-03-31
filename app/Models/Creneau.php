<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Creneau de disponibilite d'une activite.
 * Defini la fenetre horaire, la capacite et le prix effectif vendu.
 */
class Creneau extends Model
{
    protected $table = 'creneaux';

    // capacite_restante: stock temps reel; prix_applique: prix du creneau.
    protected $fillable = [
        'activite_id',
        'debut_at',
        'fin_at',
        'capacite_totale',
        'capacite_restante',
        'prix_applique',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'debut_at' => 'datetime',
            'fin_at' => 'datetime',
            'prix_applique' => 'decimal:2',
        ];
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function lignesPanier(): HasMany
    {
        return $this->hasMany(LignePanier::class);
    }

    public function lignesReservation(): HasMany
    {
        return $this->hasMany(LigneReservation::class);
    }
}
