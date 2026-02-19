<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PermissionController;

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::apiResource('users', UserController::class);
    Route::post('users/updateimg', [UserController::class, 'updateimg']);


    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('sources', \App\Http\Controllers\Api\SourceController::class);
    Route::apiResource('brands', \App\Http\Controllers\Api\BrandController::class);
    Route::apiResource('roles', RoleController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('source-list', [\App\Http\Controllers\Api\SourceController::class, 'getList']);
    Route::get('brand-list', [\App\Http\Controllers\Api\BrandController::class, 'getList']);
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions']);
    Route::put('/role-permissions', [PermissionController::class, 'updateRolePermissions']);
    Route::apiResource('permissions', PermissionController::class);

    Route::get('/user', [ProfileController::class, 'user']);
    Route::get('/user/signin', [ProfileController::class, 'user']);
    Route::put('/user', [ProfileController::class, 'update']);

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
});

Route::get('category-list', [CategoryController::class, 'getList']);

Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'index']);
Route::get('/feed/live', [\App\Http\Controllers\Api\SearchController::class, 'liveFeed']);
Route::get('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

Route::post('/ai/remove-bg', [\App\Http\Controllers\Api\AiController::class, 'removeBackground']);

// Rutas públicas de outfits (lectura)
Route::get('/outfits', [\App\Http\Controllers\Api\OutfitController::class, 'index']);
Route::get('/outfits/{id}', [\App\Http\Controllers\Api\OutfitController::class, 'show']);

// Rutas protegidas de outfits (escritura) - Requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/outfits', [\App\Http\Controllers\Api\OutfitController::class, 'store']);
    Route::put('/outfits/{id}', [\App\Http\Controllers\Api\OutfitController::class, 'update']);
    Route::delete('/outfits/{id}', [\App\Http\Controllers\Api\OutfitController::class, 'destroy']);
    Route::get('/my-outfits', [\App\Http\Controllers\Api\OutfitController::class, 'myOutfits']);
    Route::get('/feed/following', [\App\Http\Controllers\Api\OutfitController::class, 'feed']);
});

Route::post('/follow', [\App\Http\Controllers\Api\FollowController::class, 'toggle'])->middleware('auth:sanctum');

Route::get('/public/user/{id}', [\App\Http\Controllers\Api\PublicProfileController::class, 'show']);


