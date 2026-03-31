<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne detail d'une reservation.
 * Conserve le creneau reserve, la quantite et le prix unitaire historique.
 */
class LigneReservation extends Model
{
    protected $table = 'lignes_reservation';

    protected $fillable = [
        'reservation_id',
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

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function creneau(): BelongsTo
    {
        return $this->belongsTo(Creneau::class);
    }
}
