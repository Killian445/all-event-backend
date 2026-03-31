<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Paiement dedie aux campagnes publicitaires.
 * Separe des paiements de reservation pour le reporting business.
 */
class PaiementPublicite extends Model
{
    protected $table = 'paiements_publicite';

    protected $fillable = [
        'campagne_publicitaire_id',
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

    public function campagne(): BelongsTo
    {
        return $this->belongsTo(CampagnePublicitaire::class, 'campagne_publicitaire_id');
    }
}
