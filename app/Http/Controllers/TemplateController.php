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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.field_name' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|in:text,select,radio,checkbox,date,number,boolean',
            'fields.*.is_required' => 'required|boolean',
            'fields.*.display_order' => 'required|integer|min:0',
            'fields.*.options' => 'required_if:fields.*.field_type,select,radio,checkbox|nullable|array',
            'fields.*.options.*.option_name' => 'required_with:fields.*.options|string|max:255',
            'fields.*.options.*.option_value' => 'required_with:fields.*.options|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $template = Template::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['fields'] as $fieldData) {
                $field = Field::create([
                    'template_id' => $template->id,
                    'field_name' => $fieldData['field_name'],
                    'field_type' => $fieldData['field_type'],
                    'is_required' => $fieldData['is_required'],
                    'display_order' => $fieldData['display_order'],
                ]);

                if (in_array($fieldData['field_type'], ['select', 'radio', 'checkbox']) && isset($fieldData['options'])) {
                    foreach ($fieldData['options'] as $optionData) {
                        Option::create([
                            'field_id' => $field->id,
                            'option_name' => $optionData['option_name'],
                            'option_value' => $optionData['option_value']
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Template created successfully',
                'template' => $template->load('fields.options'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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
            $template = Template::with([
                'fields' => function ($query) {
                    $query->orderBy('display_order', 'asc');
                },
                'fields.options'
            ])->withCount('records')->findOrFail($id);


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
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'fields' => 'sometimes|array|min:1',
                'fields.*.field_name' => 'required|string|max:255',
                'fields.*.field_type' => 'required|string|in:text,select,radio,checkbox,date,number,boolean',
                'fields.*.is_required' => 'required|boolean',
                'fields.*.display_order' => 'required|integer|min:0',
                'fields.*.options' => 'required_if:fields.*.field_type,select,radio,checkbox|nullable|array',
                'fields.*.options.*.option_name' => 'required_with:fields.*.options|string|max:255',
                'fields.*.options.*.option_value' => 'required_with:fields.*.options|string|max:255',
            ]);

            if (isset($validated['name'])) {
                $template->update(['name' => $validated['name']]);
            }

            if (isset($validated['description'])) {
                $template->update(['description' => $validated['description']]);
            }

            if (isset($validated['fields'])) {
                // Delete existing fields and their options
                $template->fields()->delete();

                foreach ($validated['fields'] as $fieldData) {
                    $field = Field::create([
                        'template_id' => $template->id,
                        'field_name' => $fieldData['field_name'],
                        'field_type' => $fieldData['field_type'],
                        'is_required' => $fieldData['is_required'],
                        'display_order' => $fieldData['display_order'],
                    ]);

                    if (in_array($fieldData['field_type'], ['select', 'radio', 'checkbox']) && isset($fieldData['options'])) {
                        foreach ($fieldData['options'] as $optionData) {
                            Option::create([
                                'field_id' => $field->id,
                                'option_name' => $optionData['option_name'],
                                'option_value' => $optionData['option_value'],
                            ]);
                        }
                    }
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
