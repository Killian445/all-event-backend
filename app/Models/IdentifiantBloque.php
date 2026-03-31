<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Identifiants (email, telephone) interdits pour toute nouvelle inscription
 * apres blocage administrateur du compte source.
 */
class IdentifiantBloque extends Model
{
    protected $table = 'identifiants_bloques';

    protected $fillable = [
        'type',
        'valeur',
        'bloque_par_user_id',
    ];

    public function bloquePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bloque_par_user_id');
    }

    public static function normaliserEmail(?string $email): string
    {
        return strtolower(trim((string) $email));
    }

    /**
     * Chiffres uniquement pour comparer les numeros (independamment des espaces / +).
     */
    public static function normaliserTelephone(?string $telephone): ?string
    {
        if ($telephone === null || trim($telephone) === '') {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $telephone);
        if ($digits === null || strlen($digits) < 8) {
            return null;
        }

        return $digits;
    }

    public static function emailEstBloque(string $email): bool
    {
        $v = self::normaliserEmail($email);

        return self::query()->where('type', 'email')->where('valeur', $v)->exists();
    }

    public static function telephoneEstBloque(?string $telephone): bool
    {
        $v = self::normaliserTelephone($telephone);
        if ($v === null) {
            return false;
        }

        return self::query()->where('type', 'telephone')->where('valeur', $v)->exists();
    }
}
