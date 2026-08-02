<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuario de trabajo para desarrollo.
 *
 * Tras la Fase 1 todas las rutas de gestión exigen sesión iniciada, así que sin
 * al menos un usuario el panel queda inaccesible.
 *
 * Cuando la Fase 3 introduzca roles (H-14), este seeder marcará el rol de
 * administrador. De momento todo usuario autenticado tiene el mismo acceso.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@supermercado.test'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuario: admin@supermercado.test / password');
    }
}
