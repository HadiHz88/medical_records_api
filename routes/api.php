<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\ValueController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



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

Route::prefix('fields')->group(function () {
    Route::get('/{template_id}', [FieldController::class, 'index']); // Get fields for a template
    Route::post('/', [FieldController::class, 'store']); // Create a new field
    Route::put('/{id}', [FieldController::class, 'update']); // Update field
    Route::delete('/{id}', [FieldController::class, 'destroy']); // Delete field
});

Route::prefix('values')->group(function () {
    Route::post('/', [ValueController::class, 'store']); // Store values for a record
    Route::get('/{record_id}', [ValueController::class, 'index']); // Get values for a record
});
