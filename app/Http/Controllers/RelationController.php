<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelationController extends Controller
{
    // GET /api/academy/{academy}/courses -> cursos de una academia (1:N, pública)
    public function academyCourses(Academy $academy): JsonResponse
    {
        return response()->json([
            'academy' => $academy->only(['id', 'name']),
            'courses' => $academy->courses,
        ], 200);
    }

    // GET /api/course/{course}/students -> alumnos de un curso (N:N, con token)
    public function courseStudents(Course $course): JsonResponse
    {
        return response()->json([
            'course' => $course->only(['id', 'name', 'capacity']),
            'total' => $course->students()->count(),
            'students' => $course->students,
        ], 200);
    }

    // POST /api/courses/{course}/enroll/{student} -> matricular
    public function enroll(Request $request, Course $course, Student $student): JsonResponse
    {
        if (! $this->esPropietario($request, $course)) {
            return response()->json([
                'message' => 'Solo el dueño de la academia puede matricular alumnos en este curso',
            ], 403);
        }

        // ¿Ya está matriculado?
        if ($course->students()->where('students.id', $student->id)->exists()) {
            return response()->json([
                'message' => 'El alumno ya está matriculado en este curso',
            ], 409);
        }

        // ¿Quedan plazas?
        if ($course->students()->count() >= $course->capacity) {
            return response()->json([
                'message' => 'El curso está completo, no quedan plazas',
            ], 422);
        }

        // Inserta una fila en la tabla pivote course_student
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        return response()->json([
            'message' => 'Alumno matriculado correctamente',
            'course' => $course->only(['id', 'name']),
            'student' => $student->only(['id', 'name', 'surname']),
        ], 201);
    }

    // DELETE /api/courses/{course}/unenroll/{student} -> desmatricular
    public function unenroll(Request $request, Course $course, Student $student): JsonResponse
    {
        if (! $this->esPropietario($request, $course)) {
            return response()->json([
                'message' => 'Solo el dueño de la academia puede desmatricular alumnos de este curso',
            ], 403);
        }

        if (! $course->students()->where('students.id', $student->id)->exists()) {
            return response()->json([
                'message' => 'El alumno no está matriculado en este curso',
            ], 404);
        }

        // Borra la fila de la tabla pivote course_student
        $course->students()->detach($student->id);

        return response()->json([
            'message' => 'Alumno desmatriculado correctamente',
        ], 200);
    }

    // El curso es del usuario si la academia del curso es suya
    private function esPropietario(Request $request, Course $course): bool
    {
        return $course->academy->user_id === $request->user()->id;
    }
}