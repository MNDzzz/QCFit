<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Outfit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Clase OutfitTest
 * 
 * Suite de tests de integración para probar el recurso Outfit al 100% de cobertura.
 * Verifica accesos públicos, validaciones, control de acceso por permisos (Spatie)
 * y operaciones de moderación administrativa.
 */
class OutfitTest extends TestCase
{
    use RefreshDatabase;

    protected $adminRole;
    protected $userRole;

    /**
     * Configuración previa a cada test.
     * Registra los roles y permisos necesarios para simular el control de accesos.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear permisos requeridos para las pruebas
        Permission::findOrCreate('outfit-create', 'web');
        Permission::findOrCreate('outfit-list-own', 'web');
        Permission::findOrCreate('outfit-edit-own', 'web');
        Permission::findOrCreate('outfit-delete-own', 'web');
        Permission::findOrCreate('outfit-moderate', 'web');
        Permission::findOrCreate('outfit-delete-any', 'web');
        Permission::findOrCreate('outfit-edit-any', 'web');

        // Crear rol de administrador y asignarle todos los permisos
        $this->adminRole = Role::findOrCreate('admin', 'web');
        $this->adminRole->givePermissionTo(Permission::all());

        // Crear rol de usuario común y asignarle sus permisos específicos
        $this->userRole = Role::findOrCreate('user', 'web');
        $this->userRole->givePermissionTo([
            'outfit-create',
            'outfit-list-own',
            'outfit-edit-own',
            'outfit-delete-own'
        ]);
    }

    /**
     * Test 1: Los visitantes no autenticados pueden ver la lista pública de outfits.
     */
    public function test_public_can_list_outfits()
    {
        Outfit::factory()->count(3)->create();

        $response = $this->getJson('/api/outfits');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test 2: Los visitantes no autenticados pueden ver el detalle de un outfit específico.
     */
    public function test_public_can_show_outfit()
    {
        $outfit = Outfit::factory()->create();

        $response = $this->getJson("/api/outfits/{$outfit->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $outfit->id);
    }

    /**
     * Test 3: Un usuario autenticado con rol 'user' (y permiso outfit-create) puede crear un outfit.
     */
    public function test_authenticated_user_with_permission_can_create_outfit()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $products = Product::factory()->count(2)->create();

        $outfitData = [
            'title' => 'Outfit de Invierno',
            'description' => 'Estilo abrigado y moderno.',
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'x' => 100,
                    'y' => 150,
                    'rotation' => 0,
                    'scaleX' => 1,
                    'scaleY' => 1,
                    'zIndex' => 1,
                    'isFlipped' => false
                ],
                [
                    'product_id' => $products[1]->id,
                    'x' => 250,
                    'y' => 300,
                    'rotation' => 45,
                    'scaleX' => 1.5,
                    'scaleY' => 1.5,
                    'zIndex' => 2,
                    'isFlipped' => true
                ]
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/outfits', $outfitData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('outfits', [
            'title' => 'Outfit de Invierno',
            'user_id' => $user->id
        ]);

        // Verificar inserción en la tabla pivote con atributos del canvas
        $this->assertDatabaseHas('outfit_product', [
            'product_id' => $products[0]->id,
            'pos_x' => 100,
            'pos_y' => 150,
            'rotation' => 0,
            'scale_x' => 1.0,
            'scale_y' => 1.0,
            'z_index' => 1,
            'is_flipped' => false
        ]);
    }

    /**
     * Test 4: Un usuario no puede crear outfits si carece del permiso 'outfit-create'.
     */
    public function test_user_cannot_create_outfit_without_permission()
    {
        // Creamos un usuario sin asignarle rol (no tiene el permiso)
        $user = User::factory()->create();
        $products = Product::factory()->count(1)->create();

        $outfitData = [
            'title' => 'Outfit Prohibido',
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'x' => 10, 'y' => 10, 'rotation' => 0, 'scaleX' => 1, 'scaleY' => 1, 'zIndex' => 1, 'isFlipped' => false
                ]
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/outfits', $outfitData);

        $response->assertStatus(403);
    }

    /**
     * Test 5: La creación de un outfit falla ante datos de validación inválidos.
     */
    public function test_user_cannot_create_outfit_with_invalid_data()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        // Datos de petición inválidos (título ausente e items vacíos)
        $outfitData = [
            'description' => 'Sin título ni productos.',
            'items' => []
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/outfits', $outfitData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'items']);
    }

    /**
     * Test 6: El propietario de un outfit puede actualizarlo.
     */
    public function test_owner_can_update_outfit()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $outfit = Outfit::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();

        $updateData = [
            'title' => 'Outfit Actualizado',
            'description' => 'Nueva descripción.',
            'items' => [
                [
                    'product_id' => $product->id,
                    'x' => 500,
                    'y' => 500,
                    'rotation' => 90,
                    'scaleX' => 2.0,
                    'scaleY' => 2.0,
                    'zIndex' => 5,
                    'isFlipped' => false
                ]
            ]
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/outfits/{$outfit->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('outfits', [
            'id' => $outfit->id,
            'title' => 'Outfit Actualizado',
            'description' => 'Nueva descripción.'
        ]);
    }

    /**
     * Test 7: Un usuario no puede actualizar un outfit ajeno.
     */
    public function test_non_owner_cannot_update_outfit()
    {
        $owner = User::factory()->create();
        $owner->assignRole('user');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('user');

        $outfit = Outfit::factory()->create(['user_id' => $owner->id]);
        $product = Product::factory()->create();

        $updateData = [
            'title' => 'Intento de Modificación',
            'items' => [
                [
                    'product_id' => $product->id,
                    'x' => 10, 'y' => 10, 'rotation' => 0, 'scaleX' => 1, 'scaleY' => 1, 'zIndex' => 1, 'isFlipped' => false
                ]
            ]
        ];

        // Intentar actualizar usando la sesión del otro usuario
        $response = $this->actingAs($otherUser)
            ->putJson("/api/outfits/{$outfit->id}", $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test 8: El propietario de un outfit puede eliminarlo.
     */
    public function test_owner_can_delete_outfit()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $outfit = Outfit::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/outfits/{$outfit->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('outfits', ['id' => $outfit->id]);
    }

    /**
     * Test 9: Un usuario común no puede eliminar un outfit ajeno.
     */
    public function test_non_owner_cannot_delete_outfit()
    {
        $owner = User::factory()->create();
        $owner->assignRole('user');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('user');

        $outfit = Outfit::factory()->create(['user_id' => $owner->id]);

        // Intentar eliminar usando la sesión del otro usuario
        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/outfits/{$outfit->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('outfits', ['id' => $outfit->id]);
    }

    /**
     * Test 10: Un administrador con permisos de moderación puede listar todos los outfits.
     */
    public function test_admin_can_list_all_outfits_for_moderation()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Outfit::factory()->count(2)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/outfits');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test 11: Un usuario común no puede listar los outfits de moderación.
     */
    public function test_user_cannot_list_all_outfits_for_moderation()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->getJson('/api/admin/outfits');

        $response->assertStatus(403);
    }

    /**
     * Test 12: Un administrador con permisos de moderación puede eliminar cualquier outfit del sistema.
     */
    public function test_admin_can_delete_any_outfit()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $owner = User::factory()->create();
        $outfit = Outfit::factory()->create(['user_id' => $owner->id]);

        // El admin consume el endpoint administrativo de moderación
        $response = $this->actingAs($admin)
            ->deleteJson("/api/admin/outfits/{$outfit->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('outfits', ['id' => $outfit->id]);
    }

    /**
     * Test 13: Un usuario común no puede consumir el endpoint administrativo de moderación de outfits.
     */
    public function test_user_cannot_delete_any_outfit_via_moderation()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $owner = User::factory()->create();
        $outfit = Outfit::factory()->create(['user_id' => $owner->id]);

        // El usuario común consume erróneamente el endpoint de moderación
        $response = $this->actingAs($user)
            ->deleteJson("/api/admin/outfits/{$outfit->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('outfits', ['id' => $outfit->id]);
    }

    /**
     * Test 14: Un usuario puede obtener únicamente sus propios outfits.
     */
    public function test_user_can_get_their_own_outfits()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $otherUser = User::factory()->create();

        // Creamos 2 outfits para el usuario y 1 para otro usuario
        Outfit::factory()->count(2)->create(['user_id' => $user->id]);
        Outfit::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/my-outfits');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
