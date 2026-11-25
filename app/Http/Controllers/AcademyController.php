<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademyController extends Controller
{
    // GET /api/academies -> lista todas las academias
    public function index(): JsonResponse
    {
        $academies = Academy::with('user:id,name')->get();

        return response()->json($academies, 200);
    }

    // POST /api/academies -> crea una academia del usuario autenticado
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // El user_id lo pone Laravel a partir del usuario del token
        $academy = $request->user()->academies()->create($data);

        return response()->json([
            'message' => 'Academia creada correctamente',
            'academy' => $academy,
        ], 201);
    }

    // GET /api/academies/{academy} -> muestra una academia con sus cursos
    public function show(Academy $academy): JsonResponse
    {
        $academy->load('user:id,name', 'courses');

        return response()->json($academy, 200);
    }

    // PUT/PATCH /api/academies/{academy} -> actualiza (solo el creador)
    public function update(Request $request, Academy $academy): JsonResponse
    {
        if ($academy->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para modificar esta academia',
            ], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $academy->update($data);

        return response()->json([
            'message' => 'Academia actualizada correctamente',
            'academy' => $academy,
        ], 200);
    }

    // DELETE /api/academies/{academy} -> elimina (solo el creador)
    public function destroy(Request $request, Academy $academy): JsonResponse
    {
        if ($academy->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar esta academia',
            ], 403);
        }

        $academy->delete();

        return response()->json([
            'message' => 'Academia eliminada correctamente',
        ], 200);
    }
}