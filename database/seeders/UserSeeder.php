<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de trabajo para desarrollo.
 *
 * Tras la Fase 1 todas las rutas de gestión exigen sesión iniciada, así que sin
 * al menos un usuario el panel queda inaccesible. Desde la Fase 3 hay dos roles
 * (H-14), y hacen falta los dos para poder comprobar que el cliente NO ve el
 * panel: con un solo usuario administrador esa mitad no se puede probar.
 *
 * 'rol' y 'email_verified_at' se asignan fuera de la creación masiva porque no
 * están en $fillable: pasados a firstOrCreate() se descartaban en silencio y el
 * usuario quedaba sin verificar, con /dashboard cerrado tras el middleware
 * 'verified'.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['Administrador', 'admin@supermercado.test', RolUsuario::Admin],
            ['Cliente de prueba', 'cliente@supermercado.test', RolUsuario::Cliente],
        ];

        foreach ($usuarios as [$nombre, $email, $rol]) {
            $usuario = User::firstOrNew(['email' => $email]);

            $usuario->fill([
                'name' => $nombre,
                'password' => 'password',
            ]);

            $usuario->rol = $rol;
            $usuario->email_verified_at = now();
            $usuario->save();

            $this->command->info("{$rol->etiqueta()}: {$email} / password");
        }
    }
}
