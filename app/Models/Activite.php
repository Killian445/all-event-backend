<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Activite commerciale publiee par un prestataire.
 * Entite centrale du catalogue, des reservations et des avis.
 */
class Activite extends Model
{
    protected $table = 'activites';

    // prix_base: prix de reference; statut: brouillon/publiee/etc selon workflow.
    protected $fillable = [
        'prestataire_id',
        'categorie_id',
        'ville_id',
        'lieu_id',
        'titre',
        'description',
        'statut',
        'prix_base',
    ];

    protected function casts(): array
    {
        return [
            'prix_base' => 'decimal:2',
        ];
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function lieu(): BelongsTo
    {
        return $this->belongsTo(Lieu::class);
    }

    public function medias(): HasMany
    {
        return $this->hasMany(ActiviteMedia::class);
    }

    public function creneaux(): HasMany
    {
        return $this->hasMany(Creneau::class);
    }

    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class);
    }

    public function campagnesPublicitaires(): HasMany
    {
        return $this->hasMany(CampagnePublicitaire::class);
    }

    /**
     * Si l'activite etait visible catalogue, toute evolution des medias impose une nouvelle validation admin.
     */
    public function repasserEnRevalidationSiPubliee(): void
    {
        if ($this->statut !== 'publiee') {
            return;
        }
        $this->forceFill(['statut' => 'en_attente_validation'])->save();
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function evenementsStatistiques(): HasMany
    {
        return $this->hasMany(EvenementStatistique::class);
    }
}
