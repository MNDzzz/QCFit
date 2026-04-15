<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Archivo de semilla generado automáticamente para la tabla de permisos.
     * Saneado para QCFit: Se han eliminado cursos, tareas y ejercicios.
     *
     * @return void
     */
    public function run()
    {
        // Limpiamos la tabla de permisos antes de insertar
        DB::table('permissions')->delete();

        $permissions = [
            // Permisos Base de Administración (Core)
            ['id' => 1,  'name' => 'role-list', 'guard_name' => 'web'],
            ['id' => 2,  'name' => 'role-create', 'guard_name' => 'web'],
            ['id' => 3,  'name' => 'role-edit', 'guard_name' => 'web'],
            ['id' => 4,  'name' => 'role-delete', 'guard_name' => 'web'],
            ['id' => 5,  'name' => 'permission-list', 'guard_name' => 'web'],
            ['id' => 6,  'name' => 'permission-create', 'guard_name' => 'web'],
            ['id' => 7,  'name' => 'permission-edit', 'guard_name' => 'web'],
            ['id' => 8,  'name' => 'permission-delete', 'guard_name' => 'web'],
            ['id' => 9,  'name' => 'user-list', 'guard_name' => 'web'],
            ['id' => 10, 'name' => 'user-create', 'guard_name' => 'web'],
            ['id' => 11, 'name' => 'user-edit', 'guard_name' => 'web'],
            ['id' => 12, 'name' => 'user-delete', 'guard_name' => 'web'],

            // Permisos Oficiales de Administración de QCFit
            ['id' => 13, 'name' => 'product-list', 'guard_name' => 'web'],
            ['id' => 14, 'name' => 'product-create', 'guard_name' => 'web'],
            ['id' => 15, 'name' => 'product-edit', 'guard_name' => 'web'],
            ['id' => 16, 'name' => 'product-delete', 'guard_name' => 'web'],

            ['id' => 17, 'name' => 'brand-list', 'guard_name' => 'web'],
            ['id' => 18, 'name' => 'brand-create', 'guard_name' => 'web'],
            ['id' => 19, 'name' => 'brand-edit', 'guard_name' => 'web'],
            ['id' => 20, 'name' => 'brand-delete', 'guard_name' => 'web'],

            ['id' => 21, 'name' => 'category-list', 'guard_name' => 'web'],
            ['id' => 22, 'name' => 'category-create', 'guard_name' => 'web'],
            ['id' => 23, 'name' => 'category-edit', 'guard_name' => 'web'],
            ['id' => 24, 'name' => 'category-delete', 'guard_name' => 'web'],

            ['id' => 25, 'name' => 'source-list', 'guard_name' => 'web'],
            ['id' => 26, 'name' => 'source-create', 'guard_name' => 'web'],
            ['id' => 27, 'name' => 'source-edit', 'guard_name' => 'web'],
            ['id' => 28, 'name' => 'source-delete', 'guard_name' => 'web'],

            ['id' => 29, 'name' => 'outfit-delete', 'guard_name' => 'web'],
        ];

        // Inserción masiva en la tabla de permisos
        DB::table('permissions')->insert($permissions);
    }
}