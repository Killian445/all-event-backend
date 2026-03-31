<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Entite societe prestataire.
 * Porte les activites vendues et le statut de validation admin.
 */
class Prestataire extends Model
{
    protected $table = 'prestataires';

    // numero_fiscal: identifiant legal; valide_le: date de validation admin.
    protected $fillable = [
        'nom',
        'raison_sociale',
        'numero_fiscal',
        'statut',
        'valide_le',
        'motif_rejet',
    ];

    protected function casts(): array
    {
        return [
            'valide_le' => 'datetime',
        ];
    }

    public function membres(): HasMany
    {
        return $this->hasMany(PrestataireMembre::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'prestataire_membres')
            ->withPivot(['role_membre', 'rejoint_le'])
            ->withTimestamps();
    }

    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class);
    }

    public function reglesCommission(): HasMany
    {
        return $this->hasMany(RegleCommission::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function campagnesPublicitaires(): HasMany
    {
        return $this->hasMany(CampagnePublicitaire::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function evenementsStatistiques(): HasMany
    {
        return $this->hasMany(EvenementStatistique::class);
    }

    public function litiges(): HasMany
    {
        return $this->hasMany(Litige::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PrestataireDocument::class);
    }
}
