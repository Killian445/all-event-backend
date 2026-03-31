<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdentifiantBloque;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Administration des comptes utilisateurs.
 * Lecture seule + blocage definitif (suppression + liste d'identifiants interdits).
 */
class GestionUtilisateurAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with('profil')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json($query->paginate(30));
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load(['profil', 'prestataires']));
    }

    /**
     * Supprime le compte et enregistre email (et telephone profil si present)
     * comme non reutilisables pour une future inscription.
     */
    public function bloquer(Request $request, User $user): JsonResponse
    {
        $admin = $request->user();
        if ($user->id === $admin->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas bloquer votre propre compte administrateur.',
            ], 403);
        }

        if ($user->role === 'admin') {
            $admins = User::query()->where('role', 'admin')->count();
            if ($admins <= 1) {
                return response()->json([
                    'message' => 'Impossible de supprimer le dernier compte administrateur.',
                ], 422);
            }
        }

        $user->load('profil');

        DB::transaction(function () use ($user, $admin): void {
            $email = IdentifiantBloque::normaliserEmail($user->email);
            IdentifiantBloque::query()->firstOrCreate(
                [
                    'type' => 'email',
                    'valeur' => $email,
                ],
                [
                    'bloque_par_user_id' => $admin->id,
                ],
            );

            $tel = IdentifiantBloque::normaliserTelephone($user->profil?->telephone);
            if ($tel !== null) {
                IdentifiantBloque::query()->firstOrCreate(
                    [
                        'type' => 'telephone',
                        'valeur' => $tel,
                    ],
                    [
                        'bloque_par_user_id' => $admin->id,
                    ],
                );
            }

            $user->delete();
        });

        return response()->json([
            'message' => 'Compte supprime et identifiants bloques pour toute reinscription.',
        ]);
    }
}
