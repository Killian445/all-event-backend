<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message rattache a un litige.
 * Peut etre visible a tous ou interne administration.
 */
class MessageLitige extends Model
{
    protected $table = 'messages_litige';

    protected $fillable = [
        'litige_id',
        'auteur_id',
        'message',
        'interne_admin',
    ];

    protected function casts(): array
    {
        return [
            'interne_admin' => 'boolean',
        ];
    }

    public function litige(): BelongsTo
    {
        return $this->belongsTo(Litige::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}
