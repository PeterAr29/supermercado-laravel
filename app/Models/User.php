<?php

namespace App\Models;

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
    ];

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
