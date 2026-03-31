<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Media associe a une activite.
 * Supporte URL simple ou media Cloudinary avec public_id.
 */
class ActiviteMedia extends Model
{
    protected $table = 'activite_medias';

    protected $fillable = [
        'activite_id',
        'url',
        'ordre',
        'cloudinary_public_id',
    ];

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }
}
