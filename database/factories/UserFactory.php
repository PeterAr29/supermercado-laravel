<?php

namespace Database\Factories;

use App\Enums\RolUsuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Por defecto, cliente. Quien se registra en la tienda no gestiona
            // nada (H-14), y el administrador se pide explícitamente.
            'rol' => RolUsuario::Cliente,
        ];
    }

    /** Gestiona la tienda: es quien puede entrar en /admin. */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => RolUsuario::Admin,
        ]);
    }

    /** Compra en la tienda y nada más. Explícito cuando la prueba va de eso. */
    public function cliente(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => RolUsuario::Cliente,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
