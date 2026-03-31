<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Diffusion de notifications globales admin.
 * Endpoints: envoi en masse et consultation des logs.
 */
class NotificationAdminController extends Controller
{
    public function envoyerGlobale(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'cle_modele' => ['required', 'string', 'max:128'],
            'message' => ['required', 'string', 'max:5000'],
            'role' => ['nullable', 'string', 'in:client,prestataire,admin'],
        ]);

        $query = User::query()->where('status', 'active');
        if (! empty($payload['role'])) {
            $query->where('role', $payload['role']);
        }

        $users = $query->select('id')->get();
        foreach ($users as $user) {
            JournalNotification::create([
                'user_id' => $user->id,
                'canal' => 'email',
                'cle_modele' => $payload['cle_modele'],
                'payload' => ['message' => $payload['message']],
                'statut' => 'envoye',
                'envoye_le' => now(),
            ]);
        }

        return response()->json(['message' => 'Notifications enregistrees.', 'total' => $users->count()]);
    }

    public function logs(): JsonResponse
    {
        return response()->json(
            JournalNotification::query()->with('user:id,name,email')->latest()->paginate(50)
        );
    }
}
