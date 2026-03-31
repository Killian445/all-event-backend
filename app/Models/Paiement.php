<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Paiement d'une reservation.
 * Conserve le statut de transaction et les references fournisseur.
 */
class Paiement extends Model
{
    protected $table = 'paiements';

    protected $fillable = [
        'reservation_id',
        'montant',
        'devise',
        'statut',
        'fournisseur',
        'id_intention_fournisseur',
        'paye_le',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'paye_le' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function remboursements(): HasMany
    {
        return $this->hasMany(Remboursement::class);
    }
}
