<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table pivot metier entre utilisateur et prestataire.
 * Defini le role d'un membre dans une organisation prestataire.
 */
class PrestataireMembre extends Model
{
    protected $table = 'prestataire_membres';

    protected $fillable = [
        'user_id',
        'prestataire_id',
        'role_membre',
        'rejoint_le',
    ];

    protected function casts(): array
    {
        return [
            'rejoint_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }
}
