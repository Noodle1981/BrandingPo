<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Comprobar si el usuario es Administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Comprobar si el usuario es Consultor.
     */
    public function isConsultor(): bool
    {
        return $this->role === 'consultor';
    }

    /**
     * Comprobar si el usuario es Visualizador (modo solo lectura).
     */
    public function isVisualizador(): bool
    {
        return $this->role === 'visualizador';
    }

    /**
     * Comprobar si el usuario tiene permisos de escritura / mutación.
     */
    public function canWrite(): bool
    {
        return in_array($this->role, ['admin', 'consultor'], true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
