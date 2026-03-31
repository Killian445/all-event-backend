<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categorie metier des activites (ex: aventure, culture).
 * Sert au classement, au filtrage catalogue et au ciblage publicitaire.
 */
class Categorie extends Model
{
    protected $table = 'categories';

    // nom: libelle visible; slug: identifiant unique URL/API.
    protected $fillable = [
        'nom',
        'slug',
    ];

    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class);
    }

    public function campagnesPublicitaires(): HasMany
    {
        return $this->hasMany(CampagnePublicitaire::class);
    }
}
