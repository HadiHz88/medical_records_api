<?php

use App\Models\Record;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\RecordController;

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

/**
 * Grouped routes for template-related operations.
 */
Route::prefix('templates')->group(function () {
    /**
     * Get all templates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    Route::get('/', [TemplateController::class, 'index']);

    /**
     * Create a new template.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    Route::post('/', [TemplateController::class, 'store']);

    /**
     * Get a single template by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    Route::get('/{id}', [TemplateController::class, 'show']);

    /**
     * Update a template by ID.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    Route::put('/{id}', [TemplateController::class, 'update']);

    /**
     * Delete a template by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    Route::delete('/{id}', [TemplateController::class, 'destroy']);
});

/**
 * Grouped routes for record-related operations.
 */
Route::prefix('records')->group(function () {
    /**
     * Get all records.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    Route::get('/', [RecordController::class, 'index']);

    /**
     * Create a new record.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    Route::post('/', [RecordController::class, 'store']);

    /**
     * Get a single record by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    Route::get('/{id}', [RecordController::class, 'show']);

    /**
     * Delete a record by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    Route::delete('/{id}', [RecordController::class, 'destroy']);
});
