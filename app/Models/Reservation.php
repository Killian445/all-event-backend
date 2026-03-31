<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Reservation client issue d'un panier valide.
 * Regroupe lignes reservees, paiement, billet et statut metier.
 */
class Reservation extends Model
{
    protected $table = 'reservations';

    // montant_total/reduction: valorisation finale; promotion_id: promo appliquee.
    protected $fillable = [
        'user_id',
        'panier_id',
        'promotion_id',
        'statut',
        'montant_total',
        'montant_reduction',
        'devise',
    ];

    protected function casts(): array
    {
        return [
            'montant_total' => 'decimal:2',
            'montant_reduction' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneReservation::class, 'reservation_id');
    }

    public function billet(): HasOne
    {
        return $this->hasOne(Billet::class);
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class);
    }

    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }

    public function evenementsStatistiques(): HasMany
    {
        return $this->hasMany(EvenementStatistique::class);
    }

    public function litiges(): HasMany
    {
        return $this->hasMany(Litige::class);
    }

    public function remboursements(): HasMany
    {
        return $this->hasMany(Remboursement::class);
    }
}
