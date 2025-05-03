<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Template;
use App\Models\Value;
use App\Models\Field;
use App\Models\Option;
use App\Models\MultipleSelection;
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
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'fields' => 'required|array',
            'fields.*.field_id' => 'required|exists:fields,id',
            'fields.*.value' => 'required_if:fields.*.values,null',
            'fields.*.values' => 'required_if:fields.*.value,null|array',
            'fields.*.values.*' => 'string',
        ]);

        try {
            DB::beginTransaction();

            $record = Record::create([
                'template_id' => $validated['template_id'],
            ]);

            foreach ($validated['fields'] as $fieldData) {
                $field = Field::find($fieldData['field_id']);

                if ($field->is_required && empty($fieldData['value']) && empty($fieldData['values'])) {
                    throw ValidationException::withMessages([
                        'fields' => "Field {$field->field_name} is required"
                    ]);
                }

                if ($field->field_type === 'select' && $field->is_multiple) {
                    // Handle multiple selections
                    if (!empty($fieldData['values'])) {
                        $value = Value::create([
                            'record_id' => $record->id,
                            'field_id' => $field->id,
                            'value' => null, // We'll store the actual values in multiple_selections
                        ]);

                        foreach ($fieldData['values'] as $optionValue) {
                            $option = $field->options()
                                ->where('option_value', $optionValue)
                                ->first();

                            if (!$option) {
                                throw ValidationException::withMessages([
                                    'fields' => "Invalid option value '{$optionValue}' for field {$field->field_name}"
                                ]);
                            }

                            MultipleSelection::create([
                                'value_id' => $value->id,
                                'option_id' => $option->id,
                            ]);
                        }
                    }
                } else {
                    // Handle single value
                    if (!empty($fieldData['value'])) {
                        $value = Value::create([
                            'record_id' => $record->id,
                            'field_id' => $field->id,
                            'value' => $fieldData['value'],
                        ]);

                        if (in_array($field->field_type, ['select', 'radio', 'checkbox'])) {
                            $option = $field->options()
                                ->where('option_value', $fieldData['value'])
                                ->first();

                            if (!$option) {
                                throw ValidationException::withMessages([
                                    'fields' => "Invalid option value for field {$field->field_name}"
                                ]);
                            }

                            $value->update(['option_id' => $option->id]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'record' => $record->load('values.field', 'values.option', 'values.multipleSelections.option'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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
                'fields' => 'required|array',
                'fields.*.field_id' => 'required|exists:fields,id',
                'fields.*.value' => 'required_if:fields.*.values,null',
                'fields.*.values' => 'required_if:fields.*.value,null|array',
                'fields.*.values.*' => 'string',
            ]);

            foreach ($validated['fields'] as $fieldData) {
                $field = Field::find($fieldData['field_id']);
                $value = Value::where('record_id', $record->id)
                    ->where('field_id', $field->id)
                    ->first();

                if ($field->is_required && empty($fieldData['value']) && empty($fieldData['values'])) {
                    throw ValidationException::withMessages([
                        'fields' => "Field {$field->field_name} is required"
                    ]);
                }

                if ($field->field_type === 'select' && $field->is_multiple) {
                    // Handle multiple selections
                    if (!empty($fieldData['values'])) {
                        if (!$value) {
                            $value = Value::create([
                                'record_id' => $record->id,
                                'field_id' => $field->id,
                                'value' => null,
                            ]);
                        }

                        // Delete existing multiple selections
                        $value->multipleSelections()->delete();

                        // Create new multiple selections
                        foreach ($fieldData['values'] as $optionValue) {
                            $option = $field->options()
                                ->where('option_value', $optionValue)
                                ->first();

                            if (!$option) {
                                throw ValidationException::withMessages([
                                    'fields' => "Invalid option value '{$optionValue}' for field {$field->field_name}"
                                ]);
                            }

                            MultipleSelection::create([
                                'value_id' => $value->id,
                                'option_id' => $option->id,
                            ]);
                        }
                    } elseif ($value) {
                        $value->multipleSelections()->delete();
                        $value->delete();
                    }
                } else {
                    // Handle single value
                    if (!empty($fieldData['value'])) {
                        if (!$value) {
                            $value = Value::create([
                                'record_id' => $record->id,
                                'field_id' => $field->id,
                                'value' => $fieldData['value'],
                            ]);
                        } else {
                            $value->update(['value' => $fieldData['value']]);
                        }

                        if (in_array($field->field_type, ['select', 'radio', 'checkbox'])) {
                            $option = $field->options()
                                ->where('option_value', $fieldData['value'])
                                ->first();

                            if (!$option) {
                                throw ValidationException::withMessages([
                                    'fields' => "Invalid option value for field {$field->field_name}"
                                ]);
                            }

                            $value->update(['option_id' => $option->id]);
                        }
                    } elseif ($value) {
                        $value->delete();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'record' => $record->fresh(['template', 'values.field', 'values.option', 'values.multipleSelections.option']),
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