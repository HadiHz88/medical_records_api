<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Template;
use App\Models\Value;
use App\Models\Field;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordController extends Controller
{
    /**
     * Display a listing of records with related data.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $records = Record::with(['template', 'values.field', 'values.option'])->get();
        return response()->json($records);
    }

    /**
     * Store a newly created record in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'template_id' => 'required|exists:templates,id',
                'fields' => 'required|array|min:1',
                'fields.*.field_id' => 'required|exists:fields,id',
                'fields.*.value' => 'required',
            ]);

            $record = Record::create([
                'template_id' => $validated['template_id'],
            ]);

            $values = collect($validated['fields'])->map(function ($field) use ($record) {
                $fieldModel = Field::find($field['field_id']);
                $value = $field['value'];

                // If the field has options, validate that the value is a valid option
                if (in_array($fieldModel->field_type, ['select', 'radio', 'checkbox'])) {
                    $option = $fieldModel->options()
                        ->where('option_value', $value)
                        ->first();

                    if (!$option) {
                        throw ValidationException::withMessages([
                            'fields' => "Invalid option value for field {$fieldModel->field_name}"
                        ]);
                    }

                    return [
                        'record_id' => $record->id,
                        'field_id' => $field['field_id'],
                        'value' => $value,
                        'option_id' => $option->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                return [
                    'record_id' => $record->id,
                    'field_id' => $field['field_id'],
                    'value' => $value,
                    'option_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            Value::insert($values->toArray());

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'record' => $record->load(['template', 'values.field', 'values.option']),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified record with related data.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $record = Record::with(['template', 'values.field', 'values.option'])->findOrFail($id);
            return response()->json($record);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }
    }

    /**
     * Update the specified record in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $record = Record::findOrFail($id);

            $validated = $request->validate([
                'template_id' => 'sometimes|exists:templates,id',
                'fields' => 'sometimes|array',
                'fields.*.field_id' => 'required|exists:fields,id',
                'fields.*.value' => 'required',
            ]);

            if (isset($validated['template_id'])) {
                $record->update(['template_id' => $validated['template_id']]);
            }

            if (isset($validated['fields'])) {
                // Delete existing values to prevent duplicates
                Value::where('record_id', $record->id)->delete();

                $values = collect($validated['fields'])->map(function ($field) use ($record) {
                    $fieldModel = Field::find($field['field_id']);
                    $value = $field['value'];

                    // If the field has options, validate that the value is a valid option
                    if (in_array($fieldModel->field_type, ['select', 'radio', 'checkbox'])) {
                        $option = $fieldModel->options()
                            ->where('option_value', $value)
                            ->first();

                        if (!$option) {
                            throw ValidationException::withMessages([
                                'fields' => "Invalid option value for field {$fieldModel->field_name}"
                            ]);
                        }

                        return [
                            'record_id' => $record->id,
                            'field_id' => $field['field_id'],
                            'value' => $value,
                            'option_id' => $option->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    return [
                        'record_id' => $record->id,
                        'field_id' => $field['field_id'],
                        'value' => $value,
                        'option_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                Value::insert($values->toArray());
            }

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'record' => $record->fresh(['template', 'values.field', 'values.option']),
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update record',
                'error' => $e->getMessage(),
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * Remove the specified record from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $record = Record::findOrFail($id);

            // Delete associated values first
            Value::where('record_id', $id)->delete();

            // Delete the record
            $record->delete();

            DB::commit();

            return response()->json([
                'message' => 'Record deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete record',
                'error' => $e->getMessage(),
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }
}