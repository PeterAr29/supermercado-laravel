<?php

namespace App\Models;

use App\Enums\RolUsuario;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * 'rol' se queda deliberadamente fuera (H-14): si estuviera aquí, un
     * `rol=admin` colado en el formulario de registro bastaría para crearse
     * un administrador. Se asigna siempre de forma explícita.
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'rol' => RolUsuario::class,
    ];

    /**
     * ¿Gestiona la tienda? (H-14)
     *
     * La pregunta se responde aquí y no comparando strings por ahí suelto:
     * un `=== 'admin'` deja de funcionar en silencio en cuanto el campo pasa
     * a ser un enum, que es exactamente lo que ocurrió en H-37.
     */
    public function esAdmin(): bool
    {
        return $this->rol === RolUsuario::Admin;
    }

    public function esCliente(): bool
    {
        return $this->rol === RolUsuario::Cliente;
    }

    /** Compras del usuario, para "Mis pedidos" (H-10). */
    public function ventas()
    {
        return $this->hasMany(Venta::class)->latest();
    }

    /**
     * Carrito persistente del usuario (H-11).
     *
     * El AppServiceProvider ya invocaba esta relación, pero no existía:
     * Eloquent devolvía null y el contador del carrito nunca se activaba (H-08).
     */
    public function carrito()
    {
        return $this->hasOne(Carrito::class);
    }
}
