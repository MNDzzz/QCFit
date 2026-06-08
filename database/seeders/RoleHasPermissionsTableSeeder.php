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
        $userRoleId = 2;
        
        // Rango de IDs basado en los 42 permisos definidos en PermissionsTableSeeder
        $allPermissions = range(1, 42); 

        $rolePermissions = [];

        // El Administrador (Role ID 1) obtiene acceso total al sistema (IDs del 1 al 42)
        foreach ($allPermissions as $permissionId) {
            $rolePermissions[] = [
                'permission_id' => $permissionId,
                'role_id'       => $adminRoleId,
            ];
        }

        // El Usuario Común (Role ID 2) obtiene permisos específicos para interactuar con la web
        $userPermissionIds = [
            30, // outfit-create
            33, // outfit-list-own
            34, // outfit-edit-own
            35, // outfit-delete-own
            39, // favorite-toggle
            40, // follow-user
            41, // profile-edit-own
            42  // image-remove-bg
        ];

        foreach ($userPermissionIds as $permissionId) {
            $rolePermissions[] = [
                'permission_id' => $permissionId,
                'role_id'       => $userRoleId,
            ];
        }

        // Inserción de las relaciones en la base de datos
        DB::table('role_has_permissions')->insert($rolePermissions);
    }
}