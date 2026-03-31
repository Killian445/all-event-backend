<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\JournalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consultation des notifications client.
 * Endpoint principal: historique des notifications liees au compte.
 */
class NotificationClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = JournalNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(30);

        return response()->json($notifications);
    }
}
