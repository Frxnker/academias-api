<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // GET /api/courses -> lista todos los cursos
    public function index(): JsonResponse
    {
        $courses = Course::with('academy:id,name')->get();

        return response()->json($courses, 200);
    }

    // POST /api/courses -> crea un curso en una academia del usuario
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academy_id' => 'required|integer|exists:academies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'capacity' => 'required|integer|min:1',
        ]);

        // Solo puedes crear cursos en tus propias academias
        $academy = Academy::find($data['academy_id']);

        if ($academy->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No puedes crear cursos en una academia que no es tuya',
            ], 403);
        }

        $course = Course::create($data);

        return response()->json([
            'message' => 'Curso creado correctamente',
            'course' => $course,
        ], 201);
    }

    // GET /api/courses/{course} -> muestra un curso con su academia y alumnos
    public function show(Course $course): JsonResponse
    {
        $course->load('academy:id,name', 'students');

        return response()->json($course, 200);
    }

    // PUT/PATCH /api/courses/{course} -> actualiza (solo el dueño de la academia)
    public function update(Request $request, Course $course): JsonResponse
    {
        if (! $this->esPropietario($request, $course)) {
            return response()->json([
                'message' => 'No tienes permiso para modificar este curso',
            ], 403);
        }

        // academy_id no se valida: un curso no se puede mover a otra academia
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date_format:Y-m-d',
            'end_date' => 'sometimes|required|date_format:Y-m-d',
            'capacity' => 'sometimes|required|integer|min:1',
        ]);

        // Comprobar fechas combinando lo nuevo con lo que ya había
        $inicio = $data['start_date'] ?? $course->start_date->format('Y-m-d');
        $fin = $data['end_date'] ?? $course->end_date->format('Y-m-d');

        if ($fin < $inicio) {
            return response()->json([
                'message' => 'La fecha de fin no puede ser anterior a la de inicio',
            ], 422);
        }

        // La capacidad no puede ser menor que los alumnos ya matriculados
        if (isset($data['capacity']) && $data['capacity'] < $course->students()->count()) {
            return response()->json([
                'message' => 'La capacidad no puede ser menor que el número de alumnos matriculados',
            ], 422);
        }

        $course->update($data);

        return response()->json([
            'message' => 'Curso actualizado correctamente',
            'course' => $course,
        ], 200);
    }

    // DELETE /api/courses/{course} -> elimina (solo el dueño de la academia)
    public function destroy(Request $request, Course $course): JsonResponse
    {
        if (! $this->esPropietario($request, $course)) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar este curso',
            ], 403);
        }

        $course->delete();

        return response()->json([
            'message' => 'Curso eliminado correctamente',
        ], 200);
    }

    // El curso es del usuario si la academia del curso es suya
    private function esPropietario(Request $request, Course $course): bool
    {
        return $course->academy->user_id === $request->user()->id;
    }
}