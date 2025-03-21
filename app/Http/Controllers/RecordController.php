<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Template;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    public function index()
    {
        return response()->json(Record::all());
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
        ]);

        // Validate the fields data
        $validatedFields = $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*.field_id' => 'required|exists:fields,id',
            'fields.*.value' => 'required',
        ]);

        // Create a new record
        $record = Record::create([
            'template_id' => $validated['template_id'],
        ]);

        // Prepare the data for inserting field values
        $values = collect($validatedFields['fields'])->map(function ($field) use ($record) {
            return [
                'record_id' => $record->id,
                'field_id' => $field['field_id'],
                'value' => $field['value'],
            ];
        });

        // Insert the field values into the 'values' table
        Value::insert($values->toArray());

        // Return success response
        return response()->json("Record created successfully", 201);
    }


    public function show($id)
    {
        return response()->json(Record::findOrFail($id));
    }

    public function destroy($id)
    {
        Record::findOrFail($id)->delete();
        return response()->json(['message' => 'Record deleted'], 200);
    }
}
