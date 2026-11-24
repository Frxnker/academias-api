<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Academy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'email',
    ];

    // Una academia pertenece a un usuario (su creador)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Una academia tiene muchos cursos
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}