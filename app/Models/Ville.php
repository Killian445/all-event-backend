<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ville couverte par la plateforme.
 * Utilisee pour localiser activites, lieux et campagnes.
 */
class Ville extends Model
{
    protected $table = 'villes';

    // nom: nom de la ville; code_pays: norme pays ISO sur 2 caracteres.
    protected $fillable = [
        'nom',
        'code_pays',
    ];

    public function lieux(): HasMany
    {
        return $this->hasMany(Lieu::class);
    }

    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class);
    }

    public function campagnesPublicitaires(): HasMany
    {
        return $this->hasMany(CampagnePublicitaire::class);
    }

    public function evenementsStatistiques(): HasMany
    {
        return $this->hasMany(EvenementStatistique::class);
    }
}
