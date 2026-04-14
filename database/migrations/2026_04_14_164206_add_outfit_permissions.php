<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Añadir permisos de moderación de outfits para el panel admin.
     */
    public function up(): void
    {
        $permissions = [
            ['name' => 'outfit-list', 'guard_name' => 'web'],
            ['name' => 'outfit-delete', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore($permission);
        }

        // Asignar los nuevos permisos al rol admin (id=1)
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        if ($adminRoleId) {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', ['outfit-list', 'outfit-delete'])
                ->pluck('id');

            foreach ($permissionIds as $permId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permId,
                    'role_id' => $adminRoleId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['outfit-list', 'outfit-delete'])
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('name', ['outfit-list', 'outfit-delete'])->delete();
    }
};
