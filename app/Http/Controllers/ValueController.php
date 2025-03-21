<?php

namespace App\Http\Controllers;

use App\Models\Value;
use Illuminate\Http\Request;

class ValueController extends Controller
{
    public function index($record_id)
    {
        return response()->json(Value::where('record_id', $record_id)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'record_id' => 'required|exists:records,id',
            'field_id' => 'required|exists:fields,id',
            'value' => 'required|string',
        ]);

        $value = Value::create($validated);

        return response()->json($value, 201);
    }
}
