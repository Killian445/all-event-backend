<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Panier d'achat du client.
 * Etape intermediaire avant conversion en reservation.
 */
class Panier extends Model
{
    protected $table = 'paniers';

    // statut: actif/converti/etc; expire_le: limite de validite du panier.
    protected $fillable = [
        'user_id',
        'statut',
        'expire_le',
    ];

    protected function casts(): array
    {
        return [
            'expire_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LignePanier::class, 'panier_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
