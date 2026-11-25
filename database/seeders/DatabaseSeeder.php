<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios (contraseña: password)
        $frank = User::create([
            'name' => 'Frank',
            'email' => 'frank@example.com',
            'password' => 'password',
        ]);

        $ana = User::create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password',
        ]);

        // Academias (1:N con usuarios)
        $celia = $frank->academies()->create([
            'name' => 'Academia Celia',
            'description' => 'Academia de programación web',
            'address' => 'Calle Real 1, Almería',
            'phone' => '950123456',
            'email' => 'info@academiacelia.com',
        ]);

        $idiomas = $ana->academies()->create([
            'name' => 'Academia de Idiomas Ana',
            'description' => 'Inglés y francés para todos los niveles',
            'address' => 'Paseo de Almería 20, Almería',
            'phone' => '950654321',
            'email' => 'hola@idiomasana.com',
        ]);

        // Cursos (1:N con academias)
        $laravel = $celia->courses()->create([
            'name' => 'Laravel desde cero',
            'description' => 'Curso de backend con Laravel',
            'start_date' => '2027-01-15',
            'end_date' => '2027-03-30',
            'capacity' => 20,
        ]);

        $javascript = $celia->courses()->create([
            'name' => 'JavaScript moderno',
            'description' => 'ES6+, DOM y fetch',
            'start_date' => '2027-02-01',
            'end_date' => '2027-04-15',
            'capacity' => 2,
        ]);

        $ingles = $idiomas->courses()->create([
            'name' => 'Inglés B1',
            'description' => 'Preparación para el B1',
            'start_date' => '2027-01-10',
            'end_date' => '2027-06-20',
            'capacity' => 15,
        ]);

        // Alumnos
        $lucia = Student::create([
            'name' => 'Lucía',
            'surname' => 'García',
            'email' => 'lucia@example.com',
            'phone' => '600111222',
            'date_of_birth' => '2004-05-10',
        ]);

        $pablo = Student::create([
            'name' => 'Pablo',
            'surname' => 'Martínez',
            'email' => 'pablo@example.com',
            'phone' => '600333444',
            'date_of_birth' => '2003-11-22',
        ]);

        $marta = Student::create([
            'name' => 'Marta',
            'surname' => 'López',
            'email' => 'marta@example.com',
            'phone' => null,
            'date_of_birth' => '2005-02-14',
        ]);

        // Matrículas (N:N en la tabla course_student)
        $laravel->students()->attach([
            $lucia->id => ['enrolled_at' => now()],
            $pablo->id => ['enrolled_at' => now()],
        ]);

        // JavaScript queda lleno (capacidad 2) para probar el 422
        $javascript->students()->attach([
            $lucia->id => ['enrolled_at' => now()],
            $marta->id => ['enrolled_at' => now()],
        ]);

        $ingles->students()->attach([
            $marta->id => ['enrolled_at' => now()],
        ]);
    }
}