<?php

/**
 * SEEDER PRINCIPAL - DatabaseSeeder
 * 
 * Archivo: database/seeders/DatabaseSeeder.php
 * 
 * Este seeder coordina todos los demás seeders
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Ejecutar seeders en orden correcto (respetando dependencias)
        $this->call([
            // 1. Primero los datos base
            DeporteSeeder::class,

            // 2. Usuarios y perfiles
            UsuarioSeeder::class,

            // 3. Equipos (necesita deportes y usuarios)
            EquipoSeeder::class,

            // 4. Torneos (necesita deportes)
            TorneoSeeder::class,

            // 5. Relaciones muchos-a-muchos
            JugadorEquipoSeeder::class,
            EquipoTorneoSeeder::class,

            // 6. Partidos (necesita torneos, equipos, árbitros)
            PartidoSeeder::class,
        ]);

        $this->command->info('¡Todos los seeders ejecutados exitosamente!');
    }
}
