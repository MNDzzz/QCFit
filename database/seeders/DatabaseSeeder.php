<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba
        \App\Models\User::factory()->create([
            'name' => 'QCFit Tester',
            'email' => 'test@qcfit.com',
            'password' => bcrypt('password'), // Contraseña conocida
        ]);

        $this->call([
            ProductSeeder::class,
            RealProductImporterSeeder::class, // Productos reales desde CSV
                // OutfitSeeder depende de que existan usuarios y productos
            OutfitSeeder::class,
        ]);
    }
}
