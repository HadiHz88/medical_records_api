<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::with(['fields'])->withCount('records')->get();

        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $validatedTemplate = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validatedFields = $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*.field_name' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
            'fields.*.is_required' => 'boolean',
            'fields.*.display_order' => 'integer',
        ]);

        $template = Template::create($validatedTemplate);
        $fields = $template->fields()->createMany($validatedFields['fields']);

        return response()->json([
            "status" => "template successfully created",
        ], 201);
    }

    public function show($id)
    {
        $template = Template::findOrFail($id)->load('fields');
        return response()->json($template);
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        $template->update($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        $fields = $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*.field_name' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
            'fields.*.is_required' => 'boolean',
            'fields.*.display_order' => 'integer',
        ]);

        $template->fields()->delete();

        $template->fields()->createMany($fields['fields']);

        return response()->json($template);
    }

    public function destroy($id)
    {
        Template::findOrFail($id)->delete();

        return response()->json(['message' => 'Template deleted'], 200);
    }
}
