<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Profil etendu de l'utilisateur.
 * Stocke les informations personnelles non sensibles et l'avatar.
 */
class Profil extends Model
{
    protected $table = 'profils';

    // avatar: URL image; avatar_public_id: identifiant Cloudinary pour suppression.
    protected $fillable = [
        'user_id',
        'prenom',
        'nom',
        'telephone',
        'avatar',
        'avatar_public_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
