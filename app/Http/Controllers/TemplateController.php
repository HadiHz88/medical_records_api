<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    public function index()
    {
        return response()->json(Template::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $template = Template::create($validated);

        return response()->json($template, 201);
    }

    public function show($id)
    {
        return response()->json(Template::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);
        $template->update($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        return response()->json($template);
    }

    public function destroy($id)
    {
        Template::findOrFail($id)->delete();
        return response()->json(['message' => 'Template deleted'], 200);
    }
}
