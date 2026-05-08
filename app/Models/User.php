<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modelo principal de usuario.
 *
 * Integra autenticación, notificaciones, control de acceso (RBAC),
 * tokens API (Sanctum) y eliminación suave.
 */
class User extends Authenticatable
{
    /** @use HasFactory Creación de modelos falsos para tests/seeders */
    /** @use Notifiable Envío de notificaciones por correo */
    /** @use HasRoles RBAC con Spatie: roles y permisos */
    /** @use HasApiTokens Autenticación stateless vía Sanctum */
    /** @use SoftDeletes Eliminación lógica para recuperación segura */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
