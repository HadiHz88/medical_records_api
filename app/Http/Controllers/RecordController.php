<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Template;
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
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
        ]);

        $record = Record::create($validated);

        return response()->json($record, 201);
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
