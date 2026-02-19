<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OutfitController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\PublicProfileController;
use App\Http\Controllers\Api\AiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'auth:sanctum'], function () {

    // Gestión de Usuarios y Perfil
    Route::apiResource('users', UserController::class);
    Route::post('users/updateimg', [UserController::class, 'updateimg']);
    Route::get('/user', [ProfileController::class, 'user']);
    Route::put('/user', [ProfileController::class, 'update']);
    
    // Gestión de Categorías, Marcas y Marketplaces
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('sources', \App\Http\Controllers\Api\SourceController::class);
    Route::apiResource('brands', \App\Http\Controllers\Api\BrandController::class);
    
    // Gestión de Productos (Admin CRUD)
    Route::apiResource('products', ProductController::class);

    // Roles y Permisos
    Route::apiResource('roles', RoleController::class);
    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions']);
    Route::put('/role-permissions', [PermissionController::class, 'updateRolePermissions']);
    Route::apiResource('permissions', PermissionController::class);

    // Helpers para selects
    Route::get('source-list', [\App\Http\Controllers\Api\SourceController::class, 'getList']);
    Route::get('brand-list', [\App\Http\Controllers\Api\BrandController::class, 'getList']);

    Route::get('abilities', function (Request $request) {
        return $request->user()->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    });

    // Outfits protegidos
    Route::post('/outfits', [OutfitController::class, 'store']);
    Route::put('/outfits/{id}', [OutfitController::class, 'update']);
    Route::delete('/outfits/{id}', [OutfitController::class, 'destroy']);
    Route::get('/my-outfits', [OutfitController::class, 'myOutfits']);
    Route::get('/feed/following', [OutfitController::class, 'feed']);
    
    // Social
    Route::post('/follow', [FollowController::class, 'toggle']);
});

// Rutas Públicas
Route::get('category-list', [CategoryController::class, 'getList']);
Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'index']);
Route::get('/feed/live', [\App\Http\Controllers\Api\SearchController::class, 'liveFeed']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/outfits', [OutfitController::class, 'index']);
Route::get('/outfits/{id}', [OutfitController::class, 'show']);

Route::post('/ai/remove-bg', [AiController::class, 'removeBackground']);
Route::get('/public/user/{id}', [PublicProfileController::class, 'show']);
