<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Piece jointe de verification (KBIS, piece d'identite, etc.) pour validation admin.
 * Fichiers stockes sur le disque prive (non public).
 */
class PrestataireDocument extends Model
{
    protected $table = 'prestataire_documents';

    protected $fillable = [
        'prestataire_id',
        'uploaded_by_user_id',
        'libelle',
        'nom_original',
        'chemin_disque',
        'mime_type',
        'taille_octets',
    ];

    protected $hidden = [
        'chemin_disque',
    ];

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
