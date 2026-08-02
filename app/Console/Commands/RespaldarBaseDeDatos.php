<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * H-34 — Volcado de la base de desarrollo.
 *
 * El paso 0 de toda fase era una línea de mysqldump copiada a mano de
 * `04-CONVENCIONES.md`, con la ruta del binario, el usuario y el nombre del
 * fichero escritos cada vez. H-33 enseñó lo que cuesta olvidarla: la suite de
 * tests borró la base de desarrollo y no había copia posible.
 *
 *   php artisan db:respaldo --fase=7
 *   php artisan db:respaldo               (nombre con fecha y hora)
 *
 * Los volcados quedan en la raíz del proyecto y `.gitignore` los excluye: son
 * datos, y Git versiona código.
 */
class RespaldarBaseDeDatos extends Command
{
    protected $signature = 'db:respaldo
                            {--fase= : Número de fase, para nombrarlo backup_pre_fase_N.sql}
                            {--forzar : Sobrescribe el fichero si ya existe}';

    protected $description = 'Vuelca la base de datos de desarrollo a un fichero .sql (H-34)';

    public function handle(): int
    {
        $conexion = config('database.default');

        if ($conexion !== 'mysql') {
            $this->error("Solo se sabe respaldar MySQL, y la conexión activa es '{$conexion}'.");

            return self::FAILURE;
        }

        $binario = $this->binario();

        if ($binario === null) {
            $this->error('No se encontró mysqldump. Indica su ruta en DB_DUMP_BINARIO del .env.');

            return self::FAILURE;
        }

        $destino = base_path($this->nombreDelFichero());

        if (file_exists($destino) && ! $this->option('forzar')) {
            $this->error('Ya existe '.basename($destino).'. Usa --forzar para sobrescribirlo.');

            return self::FAILURE;
        }

        return $this->volcar($binario, $destino);
    }

    /**
     * Ejecuta el volcado.
     *
     * La contraseña va por variable de entorno y no como argumento: en la línea
     * de comandos la vería cualquiera que liste los procesos.
     */
    private function volcar(string $binario, string $destino): int
    {
        $base = config('database.connections.mysql.database');

        $proceso = new Process([
            $binario,
            '--host='.config('database.connections.mysql.host'),
            '--port='.config('database.connections.mysql.port'),
            '--user='.config('database.connections.mysql.username'),
            '--default-character-set=utf8mb4',
            '--single-transaction',
            $base,
        ], base_path(), ['MYSQL_PWD' => (string) config('database.connections.mysql.password')]);

        $proceso->setTimeout(300);

        $this->info("Volcando '{$base}' a ".basename($destino).'…');

        try {
            $proceso->mustRun();
        } catch (ProcessFailedException $e) {
            $this->error(trim($proceso->getErrorOutput()) ?: $e->getMessage());

            return self::FAILURE;
        }

        file_put_contents($destino, $proceso->getOutput());

        $this->info(sprintf('Listo: %s (%s KB)', $destino, number_format(filesize($destino) / 1024, 1)));

        return self::SUCCESS;
    }

    private function nombreDelFichero(): string
    {
        $fase = $this->option('fase');

        return $fase
            ? "backup_pre_fase_{$fase}.sql"
            : 'backup_'.now()->format('Y-m-d_His').'.sql';
    }

    /**
     * Dónde está mysqldump.
     *
     * Primero lo que diga el .env; si no, la ruta de XAMPP, que es lo que usa
     * el equipo; y como último recurso, el PATH.
     */
    private function binario(): ?string
    {
        $candidatos = array_filter([
            config('database.connections.mysql.dump_binario'),
            'C:/xampp/mysql/bin/mysqldump.exe',
        ]);

        foreach ($candidatos as $candidato) {
            if (is_file($candidato)) {
                return $candidato;
            }
        }

        $enPath = (new Process(['mysqldump', '--version']));
        $enPath->run();

        return $enPath->isSuccessful() ? 'mysqldump' : null;
    }
}
