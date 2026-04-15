<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleHasPermissionsTableSeeder extends Seeder
{
    /**
     * Saneamiento de la relación entre roles y permisos.
     * Vincula todos los nuevos permisos oficiales al rol de Admin.
     *
     * @return void
     */
    public function run()
    {
        // Limpiamos las relaciones previas
        DB::table('role_has_permissions')->delete();

        $adminRoleId = 1;
        // Rango de IDs basado en los 29 permisos definidos en PermissionsTableSeeder
        $allPermissions = range(1, 29); 

        $rolePermissions = [];

        // El Administrador (Role ID 1) obtiene acceso total al sistema
        foreach ($allPermissions as $permissionId) {
            $rolePermissions[] = [
                'permission_id' => $permissionId,
                'role_id'       => $adminRoleId,
            ];
        }

        // Por ahora, el rol de "user" (ID 2) no tiene permisos administrativos explícitos
        // tras el purgado de contenidos heredados (ejercicios, cursos, etc).

        // Inserción de las relaciones en la base de datos
        DB::table('role_has_permissions')->insert($rolePermissions);
    }
}