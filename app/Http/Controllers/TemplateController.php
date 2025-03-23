<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Field;
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
        $templates = Template::with(['fields'])
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
                'fields.*.field_type' => 'required|string|in:text,number,date,boolean,select',
                'fields.*.is_required' => 'boolean',
                'fields.*.display_order' => 'integer',
            ]);

            $template = Template::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            $fields = collect($validated['fields'])->map(function ($field, $index) {
                return [
                    'field_name' => $field['field_name'],
                    'field_type' => $field['field_type'],
                    'is_required' => $field['is_required'] ?? false,
                    'display_order' => $field['display_order'] ?? $index + 1,
                ];
            });

            $template->fields()->createMany($fields);

            DB::commit();

            return response()->json([
                'message' => 'Template created successfully',
                'template' => $template->load('fields'),
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
            $template = Template::with(['fields' => function ($query) {
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
                'fields.*.field_type' => 'required|string|in:text,number,date,boolean,select',
                'fields.*.is_required' => 'boolean',
                'fields.*.display_order' => 'integer',
            ]);

            if (isset($validated['name']) || isset($validated['description'])) {
                $template->update([
                    'name' => $validated['name'] ?? $template->name,
                    'description' => $validated['description'] ?? $template->description,
                ]);
            }

            if (isset($validated['fields'])) {
                // Check if template has records before deleting fields
                $hasRecords = $template->records()->exists();

                if ($hasRecords) {
                    // For templates with records, we need to be careful with field updates
                    // Get existing field IDs
                    $existingFieldIds = $template->fields()->pluck('id')->toArray();
                    $updatedFieldIds = collect($validated['fields'])->pluck('id')->filter()->toArray();

                    // Get fields to delete (fields in existing but not in updated)
                    $fieldsToDelete = array_diff($existingFieldIds, $updatedFieldIds);

                    // Check if fields to delete have values
                    $fieldsWithValues = DB::table('values')
                        ->whereIn('field_id', $fieldsToDelete)
                        ->exists();

                    if ($fieldsWithValues) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Cannot delete fields that have values associated with records',
                        ], 422);
                    }

                    // Delete fields that are safe to delete
                    Field::whereIn('id', $fieldsToDelete)->delete();

                } else {
                    // For templates without records, we can safely delete all fields
                    $template->fields()->delete();
                }

                // Create or update fields
                foreach ($validated['fields'] as $index => $fieldData) {
                    $fieldId = $fieldData['id'] ?? null;
                    $fieldAttributes = [
                        'field_name' => $fieldData['field_name'],
                        'field_type' => $fieldData['field_type'],
                        'is_required' => $fieldData['is_required'] ?? false,
                        'display_order' => $fieldData['display_order'] ?? $index + 1,
                    ];

                    if ($fieldId && $hasRecords) {
                        Field::where('id', $fieldId)
                            ->where('template_id', $template->id)
                            ->update($fieldAttributes);
                    } else {
                        $template->fields()->create($fieldAttributes);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Template updated successfully',
                'template' => $template->fresh(['fields' => function ($query) {
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

            // Delete fields first
            $template->fields()->delete();

            // Delete template
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
