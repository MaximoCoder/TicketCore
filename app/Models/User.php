<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Determinar si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Determinar si el usuario es de soporte.
     */
    public function isSupport(): bool
    {
        return $this->role === 'soporte';
    }

    /**
     * Determinar si el usuario es cliente.
     */
    public function isClient(): bool
    {
        return $this->role === 'cliente';
    }

    /**
     * Obtener el departamento al que pertenece el usuario.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }


    /**
     * Obtener los tickets creados por el usuario.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * Obtener los tickets asignados al usuario.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Obtener los comentarios del usuario en tickets.
     */
    public function ticketComments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    /**
     * Scope para usuarios activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para usuarios por rol.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
