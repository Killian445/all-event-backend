<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdentifiantBloque;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Liste des identifiants interdits a l'inscription et lever le blocage (cas
 * d'exception : reautoriser une nouvelle inscription avec le meme e-mail ou numero).
 */
class GestionIdentifiantBloqueAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = IdentifiantBloque::query()->with('bloquePar:id,name,email')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return response()->json($query->paginate(30));
    }

    public function destroy(IdentifiantBloque $identifiantBloque): JsonResponse
    {
        $identifiantBloque->delete();

        return response()->json([
            'message' => 'Blocage leve. Une nouvelle inscription avec cet identifiant est a nouveau possible.',
        ]);
    }
}
