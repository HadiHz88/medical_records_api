<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Template;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function index($template_id)
    {
        return response()->json(Field::where('template_id', $template_id)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|string',
            'is_required' => 'boolean',
            'display_order' => 'integer',
        ]);

        $field = Field::create($validated);

        return response()->json($field, 201);
    }

    public function update(Request $request, $id)
    {
        $field = Field::findOrFail($id);
        $field->update($request->validate([
            'field_name' => 'required|string|max:255',
            'field_type' => 'required|string',
            'is_required' => 'boolean',
            'display_order' => 'integer',
        ]));

        return response()->json($field);
    }

    public function destroy($id)
    {
        Field::findOrFail($id)->delete();
        return response()->json(['message' => 'Field deleted'], 200);
    }
}
