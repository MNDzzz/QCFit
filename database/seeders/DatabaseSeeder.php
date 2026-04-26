<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Semilla principal de la aplicación.
     * Organizada para garantizar el saneamiento de permisos y datos de QCFit.
     */
    public function run(): void
    {
        // 1. Roles, Permisos y Usuarios (Datos Fundacionales)
        // Se ejecutan en orden para evitar problemas de dependencias y limpiar basura heredada.
        $this->call([
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            RoleHasPermissionsTableSeeder::class,
            UsersTableSeeder::class,
            ModelHasRolesTableSeeder::class,
        ]);

        // 2. Entidades de Dominio (Datos de Negocio de QCFit)
        $this->call([
            // RealProductImporterSeeder sigue la nueva estructura relacional (FKs para Marcas y Fuentes)
            RealProductImporterSeeder::class, 
            // OutfitSeeder depende de que existan usuarios y productos previamente insertados
            OutfitSeeder::class, 
            // Interacciones sociales (seguidores y favoritos)
            SocialInteractionsSeeder::class,
        ]);
    }
}
