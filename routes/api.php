<?php

use App\Http\Controllers\Api\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/roles', [RoleController::class, 'index']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/permissions', [PermissionController::class, 'store']);
Route::get('/permissions', [PermissionController::class, 'index']);
Route::get('/permissions/{id}', [PermissionController::class, 'show']);
Route::put('/permissions/{id}', [PermissionController::class, 'update']);
