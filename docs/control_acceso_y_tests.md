# Documentación Técnica: Control de Acceso y Pruebas del Recurso Outfit

Esta guía detalla el modelo de seguridad basado en roles y permisos (Spatie Laravel Permission) y la estrategia de pruebas unitarias y de integración del recurso **Outfit** (Canvas) implementados en el proyecto QCFit.

---

## 1. Matriz de Roles y Permisos (Spatie)

Para garantizar la seguridad de las operaciones en la plataforma y cumplir con las directrices académicas, se ha estructurado una matriz de permisos granular que divide la administración general de la interacción del usuario final.

### Roles del Sistema
1.  **admin** (Administrador/Moderador): Tiene control absoluto sobre toda la plataforma, incluyendo la gestión de entidades fundacionales, administración de usuarios y moderación de contenido.
2.  **user** (Usuario Final): Usuario estándar que interactúa con la red social de moda. Puede crear outfits, seguir a otros perfiles, marcar productos favoritos y editar su propio perfil.

### Tabla Detallada de Permisos

| Permiso | Tipo | Asignado a | Descripción |
| :--- | :--- | :--- | :--- |
| **role-*** / **permission-*** | Core | `admin` | CRUD completo de roles y permisos del sistema. |
| **user-list** / **user-create** / **user-edit** / **user-delete** | Core | `admin` | CRUD administrativo de usuarios. |
| **product-create** / **product-edit** / **product-delete** | Dominio | `admin` | Gestión de catálogo de productos de importación. |
| **brand-*** / **category-*** / **source-*** | Dominio | `admin` | Gestión de marcas, categorías y marketplaces (Sources). |
| **outfit-create** | Funcional | `user`, `admin` | Permiso para guardar nuevos outfits diseñados en el Canvas. |
| **outfit-list-own** | Funcional | `user`, `admin` | Permiso para listar y ver sus propios outfits. |
| **outfit-edit-own** | Funcional | `user`, `admin` | Permiso para modificar un outfit creado por sí mismo. |
| **outfit-delete-own** | Funcional | `user`, `admin` | Permiso para eliminar un outfit de su propiedad. |
| **favorite-toggle** | Social | `user`, `admin` | Permite agregar o remover productos de la wishlist. |
| **follow-user** | Social | `user`, `admin` | Permite seguir o dejar de seguir a otros perfiles de la red. |
| **profile-edit-own** | Perfil | `user`, `admin` | Permiso para editar su alias, biografía y foto de avatar. |
| **image-remove-bg** | Herramienta | `user`, `admin` | Permite procesar y quitar el fondo de imágenes en el Studio (Canvas). |
| **outfit-moderate** | Moderación | `admin` | Permite listar todos los outfits para tareas de control. |
| **outfit-delete-any** | Moderación | `admin` | Permite eliminar outfits de otros usuarios que infrinjan políticas. |
| **outfit-edit-any** | Moderación | `admin` | Permite modificar outfits de otros usuarios (opcional). |

---

## 2. Mecanismo de Autorización en el Backend

El backend en Laravel valida estos accesos de manera elegante mediante el uso de **Políticas de Autorización (Policies)** y **Gates**.

### A. Registro de Puertas (Gates) Dinámicas
En [AuthServiceProvider](file:///c:/xampp/htdocs/PreparacionExamen/QCFit/app/Providers/AuthServiceProvider.php), registramos dinámicamente una puerta de acceso por cada permiso guardado en la base de datos:
```php
protected function registerUserAccessToGates()
{
    try {
        foreach (Permission::pluck('name') as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->roles()->whereHas('permissions', function ($q) use ($permission) {
                    $q->where('name', $permission);
                })->count() > 0;
            });
        }
    } catch (\Exception $e) {
        // Ignora errores si la BD no está migrada durante el boot.
    }
}
```

### B. Política de Outfits (OutfitPolicy)
La clase [OutfitPolicy](file:///c:/xampp/htdocs/PreparacionExamen/QCFit/app/Policies/OutfitPolicy.php) gestiona de forma limpia las reglas de negocio del recurso. Por ejemplo, la regla para la eliminación de outfits valida:
- Si el usuario tiene el permiso de moderador `outfit-delete-any` o `outfit-moderate`, puede eliminarlo sin importar el propietario.
- En caso contrario, requiere poseer el permiso `outfit-delete-own` y además ser el autor original del outfit (`user_id === auth()->id()`).

```php
public function delete(User $user, Outfit $outfit)
{
    if ($user->hasPermissionTo('outfit-delete-any') || $user->hasPermissionTo('outfit-moderate')) {
        return true;
    }
    return $user->hasPermissionTo('outfit-delete-own') && $outfit->user_id === $user->id;
}
```

### C. Aplicación en Controladores
En [OutfitController](file:///c:/xampp/htdocs/PreparacionExamen/QCFit/app/Http/Controllers/Api/OutfitController.php), se invocan estas validaciones a través del ayudante `$this->authorize(...)`:
- `store()` -> `$this->authorize('create', Outfit::class);`
- `update()` -> `$this->authorize('update', $outfit);`
- `destroy()` -> `$this->authorize('delete', $outfit);`
- `adminIndex()` -> `$this->authorize('viewAny', Outfit::class);`
- `adminDestroy()` -> `$this->authorize('delete', $outfit);`

---

## 3. Cobertura de la Suite de Tests (OutfitTest)

Se ha implementado una cobertura de pruebas exhaustiva en [OutfitTest.php](file:///c:/xampp/htdocs/PreparacionExamen/QCFit/tests/Feature/OutfitTest.php) que verifica cada una de las ramificaciones y niveles de acceso:

1.  **test_public_can_list_outfits**: Verifica que los listados públicos no requieran autenticación.
2.  **test_public_can_show_outfit**: Verifica que el detalle de un outfit sea visible libremente.
3.  **test_authenticated_user_with_permission_can_create_outfit**: Valida la creación exitosa del outfit por un usuario autenticado y verifica que los datos pivote del Canvas (`pos_x`, `pos_y`, `rotation`, `scale_x`, `scale_y`, `z_index`, `is_flipped`) se guarden con `sync()` en la tabla `outfit_product`.
4.  **test_user_cannot_create_outfit_without_permission**: Verifica el bloqueo (HTTP 403) si el usuario no tiene rol/permiso de creador.
5.  **test_user_cannot_create_outfit_with_invalid_data**: Verifica la validación de campos del `FormRequest` (HTTP 422).
6.  **test_owner_can_update_outfit**: Comprueba que el autor del outfit pueda editar los textos y posiciones del canvas.
7.  **test_non_owner_cannot_update_outfit**: Impide (HTTP 403) que un usuario común altere un outfit ajeno.
8.  **test_owner_can_delete_outfit**: Valida la eliminación por parte del dueño (HTTP 200).
9.  **test_non_owner_cannot_delete_outfit**: Bloquea (HTTP 403) la eliminación de outfits de terceros por usuarios comunes.
10. **test_admin_can_list_all_outfits_for_moderation**: Permite al administrador acceder a la sección de moderación.
11. **test_user_cannot_list_all_outfits_for_moderation**: Bloquea a los usuarios comunes del panel de moderación de outfits.
12. **test_admin_can_delete_any_outfit**: Comprueba que el administrador/moderador pueda eliminar outfits inapropiados de cualquier usuario.
13. **test_user_cannot_delete_any_outfit_via_moderation**: Bloquea a los usuarios de consumir la ruta de administración para eliminar outfits ajenos.
14. **test_user_can_get_their_own_outfits**: Valida que el listado personal devuelva única y exclusivamente los outfits creados por la sesión activa.

---

## 4. Instrucciones para la Ejecución de Pruebas

Para validar el correcto funcionamiento de las políticas y permisos en tu máquina local, ejecuta el siguiente comando en la consola de comandos de tu sistema desde la raíz del proyecto:

```bash
php artisan test --filter=OutfitTest
```

Si deseas reiniciar toda la base de datos local junto con la inserción de las semillas (seeders) saneadas y configuradas con los nuevos permisos, corre:

```bash
php artisan migrate:fresh --seed
```
