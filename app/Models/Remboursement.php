<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Demande de remboursement suite a reservation/paiement.
 * Suivie et traitee par l'administration.
 */
class Remboursement extends Model
{
    protected $table = 'remboursements';

    protected $fillable = [
        'paiement_id',
        'reservation_id',
        'demandeur_id',
        'montant',
        'statut',
        'motif',
        'traite_le',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'traite_le' => 'datetime',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }
}
