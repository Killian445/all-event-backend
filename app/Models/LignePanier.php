<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne de panier.
 * Lie un creneau choisi avec une quantite et un prix snapshot.
 */
class LignePanier extends Model
{
    protected $table = 'lignes_panier';

    // prix_unitaire_snapshot: conserve le prix au moment de l'ajout.
    protected $fillable = [
        'panier_id',
        'creneau_id',
        'quantite',
        'prix_unitaire_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire_snapshot' => 'decimal:2',
        ];
    }

    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    public function creneau(): BelongsTo
    {
        return $this->belongsTo(Creneau::class);
    }
}
