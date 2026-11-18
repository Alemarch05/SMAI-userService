<?php

use App\Http\Controllers\Api\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/permissions', [PermissionController::class, 'store']);
Route::get('/permissions', [PermissionController::class, 'index']);
Route::get('/permissions/{id}', [PermissionController::class, 'show']);
Route::put('/permissions/{id}', [PermissionController::class, 'update']);
Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);


Route::middleware('auth:api')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{roleId}/permissions', [RoleController::class, 'getRolePermissions']);
    Route::post('/roles/{roleId}/permissions', [RoleController::class, 'updatePermissions']);

    Route::post('/permissions', [PermissionController::class, 'store']);
    
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id?}', [UserController::class, 'show'])->whereNumber('id');

    Route::middleware('role:admin')->group(function () {
        Route::post('/users/{id?}', [UserController::class, 'store'])->whereNumber('id');
        Route::match(['put', 'patch'], '/users/{id?}', [UserController::class, 'update'])->whereNumber('id');
        Route::delete('/users/{id?}', [UserController::class, 'destroy'])->whereNumber('id');
    });
});
