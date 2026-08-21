<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active_workspace_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Workspaces a los que tiene acceso este usuario.
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot('role');
    }

    /**
     * Workspace actualmente activo en sesión.
     */
    public function activeWorkspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'active_workspace_id');
    }

    /**
     * Obtiene el rol del usuario dentro del workspace activo.
     * Si no tiene workspace, usa el rol global de la tabla users.
     */
    public function getRolEnWorkspaceActivo(): string
    {
        if ($this->active_workspace_id) {
            $pivot = $this->workspaces()
                ->where('workspaces.id', $this->active_workspace_id)
                ->first()?->pivot;
            if ($pivot) {
                return $pivot->role;
            }
        }
        return $this->role ?? 'visualizador';
    }

    /**
     * Comprobar si el usuario es Administrador global o en workspace activo.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->getRolEnWorkspaceActivo() === 'admin';
    }

    public function esAdmin(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Comprobar si el usuario es Consultor en workspace activo o global.
     */
    public function isConsultor(): bool
    {
        return $this->getRolEnWorkspaceActivo() === 'consultor' || $this->role === 'consultor';
    }

    /**
     * Comprobar si el usuario es Visualizador (modo solo lectura).
     */
    public function isVisualizador(): bool
    {
        return $this->getRolEnWorkspaceActivo() === 'visualizador' && $this->role !== 'admin';
    }

    /**
     * Comprobar si el usuario tiene permisos de escritura / mutación.
     */
    public function canWrite(): bool
    {
        return in_array($this->getRolEnWorkspaceActivo(), ['admin', 'consultor'], true) || $this->role === 'admin';
    }

    public function puedeEscribir(): bool
    {
        return $this->canWrite();
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
