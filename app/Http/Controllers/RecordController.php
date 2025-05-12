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
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'fields' => 'required|array',
            'fields.*.field_id' => 'required|exists:fields,id',
            'fields.*.value' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Get the template to validate that all required fields are provided
            $template = Template::with('fields')->findOrFail($validated['template_id']);

            $record = Record::create([
                'template_id' => $validated['template_id'],
            ]);

            // Create a lookup array for quick field access
            $providedFields = collect($validated['fields'])->keyBy('field_id');

            // Check that all required fields are provided
            foreach ($template->fields as $templateField) {
                if ($templateField->is_required && !$providedFields->has($templateField->id)) {
                    throw ValidationException::withMessages([
                        'fields' => "Field {$templateField->field_name} is required"
                    ]);
                }
            }

            foreach ($validated['fields'] as $fieldData) {
                $field = Field::find($fieldData['field_id']);

                if ($field->is_required && empty($fieldData['value'])) {
                    throw ValidationException::withMessages([
                        'fields' => "Field {$field->field_name} is required"
                    ]);
                }

                if (!empty($fieldData['value'])) {
                    $value = Value::create([
                        'record_id' => $record->id,
                        'field_id' => $field->id,
                        'value' => $fieldData['value'],
                    ]);

                    if (in_array($field->field_type, ['select', 'radio', 'checkbox'])) {
                        // Handle single value options (select, radio)
                        if (in_array($field->field_type, ['select', 'radio'])) {
                            $option = $field->options()
                                ->where('option_value', $fieldData['value'])
                                ->first();

                            if (!$option) {
                                throw ValidationException::withMessages([
                                    'fields' => "Invalid option value '{$fieldData['value']}' for field {$field->field_name}"
                                ]);
                            }

                            $value->update(['option_id' => $option->id]);
                        } // Handle multi-value options (checkbox) - future enhancement
                        else if ($field->field_type === 'checkbox') {
                            $option = $field->options()
                                ->where('option_value', $fieldData['value'])
                                ->first();

                            if (!$option) {
                                throw ValidationException::withMessages([
                                    'fields' => "Invalid option value '{$fieldData['value']}' for field {$field->field_name}"
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
                'record' => $record->load(['template', 'values.field', 'values.option']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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

            $record = Record::with('template.fields')->findOrFail($id);

            $validated = $request->validate([
                'fields' => 'required|array',
                'fields.*.field_id' => 'required|exists:fields,id',
                'fields.*.value' => 'required',
            ]);

            // Create a lookup array for quick field access
            $providedFields = collect($validated['fields'])->keyBy('field_id');

            // Check that all required fields are provided
            foreach ($record->template->fields as $templateField) {
                if ($templateField->is_required && !$providedFields->has($templateField->id)) {
                    throw ValidationException::withMessages([
                        'fields' => "Field {$templateField->field_name} is required"
                    ]);
                }
            }

            foreach ($validated['fields'] as $fieldData) {
                $field = Field::find($fieldData['field_id']);
                $value = Value::where('record_id', $record->id)
                    ->where('field_id', $field->id)
                    ->first();

                if ($field->is_required && empty($fieldData['value'])) {
                    throw ValidationException::withMessages([
                        'fields' => "Field {$field->field_name} is required"
                    ]);
                }

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
                    // Handle single value options (select, radio)
                    if (in_array($field->field_type, ['select', 'radio'])) {
                        $option = $field->options()
                            ->where('option_value', $fieldData['value'])
                            ->first();

                        if (!$option) {
                            throw ValidationException::withMessages([
                                'fields' => "Invalid option value '{$fieldData['value']}' for field {$field->field_name}"
                            ]);
                        }

                        $value->update(['option_id' => $option->id]);
                    }

                } elseif ($value) {
                    $value->delete();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'record' => $record->fresh(['template', 'values.field', 'values.option']),
            ]);
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
     * Remove the specified record from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $record = Record::findOrFail($id);
            $record->delete();

            return response()->json([
                'message' => 'Record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }
    }
}
