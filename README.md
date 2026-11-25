# API REST de Gestión de Academias y Cursos

Práctica final de Desarrollo Web en Entorno Servidor (2º DAW, IES Celia Viñas).

API RESTful hecha con **Laravel**, **Laravel Sail** y **Laravel Sanctum** para gestionar academias, cursos y alumnos. No tiene vistas: todas las respuestas son JSON.

## Requisitos

- Docker Desktop (en Windows, con WSL 2)
- Git
- Postman

## Instalación

```bash
git clone https://github.com/Frxnker/academias-api.git
cd academias-api

# Instalar dependencias de Composer sin tener PHP en local
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

cp .env.example .env

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

La API queda disponible en `http://localhost/api`.

## Usuarios de prueba

| Nombre | Email | Contraseña | Academia |
|---|---|---|---|
| Frank | frank@example.com | password | Academia Celia (id 1) |
| Ana | ana@example.com | password | Academia de Idiomas Ana (id 2) |

## Modelo de datos

- `users` 1:N `academies`: un usuario crea varias academias.
- `academies` 1:N `courses`: una academia tiene varios cursos.
- `courses` N:N `students`: tabla intermedia `course_student` con el campo `enrolled_at`.

## Autenticación

Se usa **Sanctum** con tokens. Al hacer `register` o `login` se devuelve un `token`, que hay que enviar en las rutas protegidas con la cabecera:

```
Authorization: Bearer {token}
Accept: application/json
```

## Endpoints

### Autenticación

| Método | Ruta | Token | Descripción |
|---|---|---|---|
| POST | `/api/register` | No | Registra un usuario y devuelve su token |
| POST | `/api/login` | No | Inicia sesión y devuelve un token |
| POST | `/api/logout` | Sí | Cierra sesión y borra el token |
| GET | `/api/user` | Sí | Datos del usuario autenticado |

### Academias

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/academies` | Lista todas las academias |
| POST | `/api/academies` | Crea una academia del usuario autenticado |
| GET | `/api/academies/{id}` | Muestra una academia con sus cursos |
| PUT/PATCH | `/api/academies/{id}` | Actualiza (solo el creador) |
| DELETE | `/api/academies/{id}` | Elimina (solo el creador) |

### Cursos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/courses` | Lista todos los cursos |
| POST | `/api/courses` | Crea un curso en una academia propia |
| GET | `/api/courses/{id}` | Muestra un curso con su academia y alumnos |
| PUT/PATCH | `/api/courses/{id}` | Actualiza (solo el dueño de la academia) |
| DELETE | `/api/courses/{id}` | Elimina (solo el dueño de la academia) |

### Alumnos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/students` | Lista todos los alumnos |
| POST | `/api/students` | Crea un alumno |

### Relaciones

| Método | Ruta | Token | Descripción |
|---|---|---|---|
| GET | `/api/academy/{academy}/courses` | No | Cursos de una academia |
| GET | `/api/course/{course}/students` | Sí | Alumnos de un curso |
| POST | `/api/courses/{course}/enroll/{student}` | Sí (dueño) | Matricula a un alumno |
| DELETE | `/api/courses/{course}/unenroll/{student}` | Sí (dueño) | Desmatricula a un alumno |

## Códigos HTTP

| Código | Cuándo |
|---|---|
| 200 | Petición correcta |
| 201 | Recurso creado (registro, academia, curso, alumno, matrícula) |
| 401 | Sin token, token no válido o credenciales incorrectas |
| 403 | El recurso no es del usuario |
| 404 | El recurso no existe, o el alumno no está matriculado |
| 409 | El alumno ya está matriculado en el curso |
| 422 | Error de validación o curso sin plazas |

## Postman

La colección está en `postman/academias-api.postman_collection.json`. Se importa en Postman con **Import**. Después de hacer login, pega el token en la pestaña **Authorization** de la colección.