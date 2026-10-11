<?php

use App\Http\Controllers\Api\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\AuthController;

Route::post('/auth/login', [AuthController::class, 'login']);


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

});