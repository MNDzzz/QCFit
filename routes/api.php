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
    Route::apiResource('roles', RoleController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
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

Route::get('/outfits', [\App\Http\Controllers\Api\OutfitController::class, 'index']);
Route::get('/outfits/{id}', [\App\Http\Controllers\Api\OutfitController::class, 'show']);

Route::post('/follow', [\App\Http\Controllers\Api\FollowController::class, 'toggle'])->middleware('auth:sanctum');

Route::get('/public/user/{id}', [\App\Http\Controllers\Api\PublicProfileController::class, 'show']);

