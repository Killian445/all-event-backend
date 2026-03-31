<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Favori client.
 * Permet de sauvegarder rapidement une activite pour consultation ulterieure.
 */
class Favori extends Model
{
    protected $table = 'favoris';

    protected $fillable = [
        'user_id',
        'activite_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }
}
