<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // GET /api/students -> lista todos los alumnos
    public function index(): JsonResponse
    {
        return response()->json(Student::all(), 200);
    }

    // POST /api/students -> crea un alumno
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date_format:Y-m-d|before:today',
        ]);

        $student = Student::create($data);

        return response()->json([
            'message' => 'Alumno creado correctamente',
            'student' => $student,
        ], 201);
    }
}