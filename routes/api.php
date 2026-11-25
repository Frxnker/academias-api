<?php

use App\Http\Controllers\AcademyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Cursos de una academia (pública, sin token)
Route::get('/academy/{academy}/courses', [RelationController::class, 'academyCourses']);

// Rutas protegidas con token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // CRUD de academias
    Route::apiResource('academies', AcademyController::class);

    // CRUD de cursos
    Route::apiResource('courses', CourseController::class);

    // Alumnos (crear y listar)
    Route::apiResource('students', StudentController::class)->only(['index', 'store']);

    // Relación N:N cursos - alumnos
    Route::get('/course/{course}/students', [RelationController::class, 'courseStudents']);
    Route::post('/courses/{course}/enroll/{student}', [RelationController::class, 'enroll']);
    Route::delete('/courses/{course}/unenroll/{student}', [RelationController::class, 'unenroll']);
});