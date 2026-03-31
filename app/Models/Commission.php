<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Repartition financiere apres paiement.
 * Separe part plateforme et part nette prestataire.
 */
class Commission extends Model
{
    protected $table = 'commissions';

    protected $fillable = [
        'paiement_id',
        'prestataire_id',
        'montant_plateforme',
        'montant_net_prestataire',
        'devise',
    ];

    protected function casts(): array
    {
        return [
            'montant_plateforme' => 'decimal:2',
            'montant_net_prestataire' => 'decimal:2',
        ];
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }
}
