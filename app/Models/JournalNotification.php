<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historique d'envoi des notifications systeme.
 * Trace le canal, le template et le statut d'acheminement.
 */
class JournalNotification extends Model
{
    protected $table = 'journal_notifications';

    protected $fillable = [
        'user_id',
        'canal',
        'cle_modele',
        'payload',
        'statut',
        'envoye_le',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'envoye_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
