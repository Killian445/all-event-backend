<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Billet emis apres paiement valide de la reservation.
 * code_public et payload_qr servent au controle d'acces.
 */
class Billet extends Model
{
    protected $table = 'billets';

    protected $fillable = [
        'reservation_id',
        'code_public',
        'payload_qr',
        'statut',
        'emis_le',
    ];

    protected function casts(): array
    {
        return [
            'emis_le' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
