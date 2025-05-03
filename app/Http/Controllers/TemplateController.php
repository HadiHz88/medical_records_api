<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Field;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates with related fields and record count.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $templates = Template::with(['fields.options'])
            ->withCount('records')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($templates);
    }

    /**
     * Store a newly created template with fields in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'fields' => 'required|array|min:1',
                'fields.*.field_name' => 'required|string|max:255',
                'fields.*.field_type' => 'required|string|in:text,number,date,boolean,select,radio,checkbox',
                'fields.*.is_required' => 'boolean',
                'fields.*.display_order' => 'integer',
                'fields.*.options' => 'nullable|array', // Only for select, radio, checkbox
                'fields.*.options.*.option_name' => 'required|string', // Option name for select, radio, checkbox
                'fields.*.options.*.option_value' => 'required|string', // Option value for select, radio, checkbox
                'fields.*.options.*.display_order' => 'boolean', // Option display order
            ]);

            $template = Template::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['fields'] as $index => $fieldData) {
                $field = $template->fields()->create([
                    'field_name' => $fieldData['field_name'],
                    'field_type' => $fieldData['field_type'],
                    'is_required' => $fieldData['is_required'] ?? false,
                    'display_order' => $fieldData['display_order'] ?? $index + 1,
                ]);

                // Create options for select/radio/checkbox fields
                if (in_array($fieldData['field_type'], ['select', 'radio', 'checkbox']) && isset($fieldData['options'])) {
                    $options = collect($fieldData['options'])->map(function ($option, $optionIndex) use ($field) {
                        return [
                            'field_id' => $field->id,
                            'option_name' => $option['option_name'],
                            'option_value' => $option['option_value'],
                            'display_order' => $option['display_order'] ?? false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    });

                    Option::insert($options->toArray());
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Template created successfully',
                'template' => $template->load(['fields.options']),
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
                'message' => 'Failed to create template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified template with its fields.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $template = Template::with(['fields.options' => function ($query) {
                $query->orderBy('display_order', 'asc');
            }])->withCount('records')->findOrFail($id);

            return response()->json($template);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Template not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified template and its fields in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $template = Template::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'fields' => 'sometimes|required|array|min:1',
                'fields.*.id' => 'nullable|integer|exists:fields,id',
                'fields.*.field_name' => 'required|string|max:255',
                'fields.*.field_type' => 'required|string|in:text,number,date,boolean,select,radio,checkbox',
                'fields.*.is_required' => 'boolean',
                'fields.*.display_order' => 'integer',
                'fields.*.options' => 'nullable|array', // Only for select, radio, checkbox
                'fields.*.options.*.id' => 'nullable|integer|exists:options,id',
                'fields.*.options.*.option_name' => 'required|string',
                'fields.*.options.*.option_value' => 'required|string',
                'fields.*.options.*.display_order' => 'boolean',
            ]);

            if (isset($validated['name']) || isset($validated['description'])) {
                $template->update([
                    'name' => $validated['name'] ?? $template->name,
                    'description' => $validated['description'] ?? $template->description,
                ]);
            }

            if (isset($validated['fields'])) {
                $existingFieldIds = $template->fields()->pluck('id')->toArray();
                $updatedFieldIds = [];

                foreach ($validated['fields'] as $index => $fieldData) {
                    $fieldId = $fieldData['id'] ?? null;
                    $fieldAttributes = [
                        'field_name' => $fieldData['field_name'],
                        'field_type' => $fieldData['field_type'],
                        'is_required' => $fieldData['is_required'] ?? false,
                        'display_order' => $fieldData['display_order'] ?? $index + 1,
                    ];

                    if ($fieldId) {
                        $field = Field::find($fieldId);
                        $field->update($fieldAttributes);
                        $updatedFieldIds[] = $fieldId;

                        // Handle options for select/radio/checkbox fields
                        if (in_array($fieldData['field_type'], ['select', 'radio', 'checkbox']) && isset($fieldData['options'])) {
                            $existingOptionIds = $field->options()->pluck('id')->toArray();
                            $updatedOptionIds = [];

                            foreach ($fieldData['options'] as $optionIndex => $optionData) {
                                $optionId = $optionData['id'] ?? null;
                                $optionAttributes = [
                                    'option_name' => $optionData['option_name'],
                                    'option_value' => $optionData['option_value'],
                                    'display_order' => $optionData['display_order'] ?? false,
                                ];

                                if ($optionId) {
                                    $option = Option::find($optionId);
                                    $option->update($optionAttributes);
                                    $updatedOptionIds[] = $optionId;
                                } else {
                                    $option = $field->options()->create($optionAttributes);
                                    $updatedOptionIds[] = $option->id;
                                }
                            }

                            // Delete options that were not included in the update
                            $optionsToDelete = array_diff($existingOptionIds, $updatedOptionIds);
                            if (!empty($optionsToDelete)) {
                                Option::whereIn('id', $optionsToDelete)->delete();
                            }
                        } else {
                            // If field type changed to non-option type, delete all options
                            $field->options()->delete();
                        }
                    } else {
                        $field = $template->fields()->create($fieldAttributes);

                        // Create options for new select/radio/checkbox fields
                        if (in_array($fieldData['field_type'], ['select', 'radio', 'checkbox']) && isset($fieldData['options'])) {
                            $options = collect($fieldData['options'])->map(function ($option, $optionIndex) use ($field) {
                                return [
                                    'field_id' => $field->id,
                                    'option_name' => $option['option_name'],
                                    'option_value' => $option['option_value'],
                                    'display_order' => $option['display_order'] ?? false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            });

                            Option::insert($options->toArray());
                        }
                    }
                }

                // Delete fields that were not included in the update
                $fieldsToDelete = array_diff($existingFieldIds, $updatedFieldIds);
                if (!empty($fieldsToDelete)) {
                    Field::whereIn('id', $fieldsToDelete)->delete();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Template updated successfully',
                'template' => $template->fresh(['fields.options' => function ($query) {
                    $query->orderBy('display_order', 'asc');
                }]),
            ]);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Template not found',
            ], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified template from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $template = Template::findOrFail($id);

            // Check if template has records
            if ($template->records()->exists()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Cannot delete template that has records associated with it',
                ], 422);
            }

            // Delete the template (cascade will handle fields and options)
            $template->delete();

            DB::commit();

            return response()->json([
                'message' => 'Template deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Template not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}