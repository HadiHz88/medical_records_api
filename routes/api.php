<?php

use App\Models\Record;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\RecordController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function () {
   $templatesCount = Template::count();
    $recordsCount = Record::count();

    return response()->json([
        'templatesCount' => $templatesCount,
        'recordsCount' => $recordsCount,
    ]);
});

Route::prefix('templates')->group(function () {
    Route::get('/', [TemplateController::class, 'index']); // Get all templates
    Route::post('/', [TemplateController::class, 'store']); // Create a new template
    Route::get('/{id}', [TemplateController::class, 'show']); // Get a single template
    Route::put('/{id}', [TemplateController::class, 'update']); // Update template
    Route::delete('/{id}', [TemplateController::class, 'destroy']); // Delete template
});

Route::prefix('records')->group(function () {
    Route::get('/', [RecordController::class, 'index']); // Get all records
    Route::post('/', [RecordController::class, 'store']); // Create a new record
    Route::get('/{id}', [RecordController::class, 'show']); // Get a single record
    Route::delete('/{id}', [RecordController::class, 'destroy']); // Delete record
});
