<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Dossier de litige lie a une reservation.
 * Suit l'echange client/prestataire/admin jusqu'a resolution.
 */
class Litige extends Model
{
    protected $table = 'litiges';

    protected $fillable = [
        'reservation_id',
        'client_id',
        'prestataire_id',
        'admin_id',
        'sujet',
        'description',
        'statut',
        'priorite',
        'resolution',
        'ferme_le',
    ];

    protected function casts(): array
    {
        return [
            'ferme_le' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MessageLitige::class);
    }
}
