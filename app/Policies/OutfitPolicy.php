<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Outfit;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Clase OutfitPolicy
 * 
 * Centraliza la lógica de autorización para el recurso Outfit de QCFit.
 * Utiliza los permisos asignados mediante Spatie Laravel Permission.
 */
class OutfitPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver la lista de outfits para moderación.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        // Se requiere permiso de moderación de outfits
        return $user->hasPermissionTo('outfit-moderate');
    }

    /**
     * Determina si el usuario puede crear outfits.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        // Se requiere el permiso específico de creación de outfits
        return $user->hasPermissionTo('outfit-create');
    }

    /**
     * Determina si el usuario puede actualizar un outfit específico.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Outfit  $outfit
     * @return bool
     */
    public function update(User $user, Outfit $outfit)
    {
        // El administrador con permisos generales puede editar cualquier outfit
        if ($user->hasPermissionTo('outfit-edit-any')) {
            return true;
        }

        // Un usuario común puede editar si tiene el permiso propio y el outfit le pertenece
        return $user->hasPermissionTo('outfit-edit-own') && $outfit->user_id === $user->id;
    }

    /**
     * Determina si el usuario puede eliminar un outfit específico.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Outfit  $outfit
     * @return bool
     */
    public function delete(User $user, Outfit $outfit)
    {
        // El administrador con permisos generales puede eliminar cualquier outfit
        if ($user->hasPermissionTo('outfit-delete-any') || $user->hasPermissionTo('outfit-moderate')) {
            return true;
        }

        // Un usuario común puede eliminar si tiene el permiso propio y el outfit le pertenece
        return $user->hasPermissionTo('outfit-delete-own') && $outfit->user_id === $user->id;
    }
}
