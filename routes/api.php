<?php

use App\Models\Record;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AuthController;

/**
 * Endpoint to get the count of templates and records.
 *
 * @return \Illuminate\Http\JsonResponse
 */
Route::get('/counts', function () {
    $templatesCount = Template::count();
    $recordsCount = Record::count();

    return response()->json([
        'templatesCount' => $templatesCount,
        'recordsCount' => $recordsCount,
    ]);
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Template routes with RBAC
    Route::middleware('admin')->group(function () {
        Route::post('/templates', [TemplateController::class, 'store']);
        Route::put('/templates/{template}', [TemplateController::class, 'update']);
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy']);
    });

    // Template permission routes
    Route::middleware('admin')->group(function () {
        Route::post('/templates/{template}/permissions', [PermissionController::class, 'assign']);
        Route::delete('/templates/{template}/permissions/{user}', [PermissionController::class, 'revoke']);
        Route::get('/templates/{template}/permissions', [PermissionController::class, 'index']);
    });

    // Template access routes
    Route::middleware('template.access')->group(function () {
        Route::get('/templates', [TemplateController::class, 'index']);
        Route::get('/templates/{template}', [TemplateController::class, 'show']);
    });

    // Record routes - accessible by both admin and users with template access
    Route::middleware(['admin', 'template.access'])->group(function () {
        Route::get('/records', [RecordController::class, 'index']);
        Route::post('/records', [RecordController::class, 'store']);
        Route::get('/records/{record}', [RecordController::class, 'show']);
        Route::put('/records/{record}', [RecordController::class, 'update']);
        Route::delete('/records/{record}', [RecordController::class, 'destroy']);
    });
});
