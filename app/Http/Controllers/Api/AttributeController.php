<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->current_company_id;
        $attributes = Attribute::where('company_id', $companyId)
            ->with('values')
            ->get();
        return response()->json($attributes);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->current_company_id;
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string|max:255',
        ]);

        // Check if attribute with same name already exists for this company
        $exists = Attribute::where('company_id', $companyId)->where('name', $request->name)->exists();
        if ($exists) {
            return response()->json([
                'message' => 'An attribute with this name already exists.'
            ], 422);
        }

        $attribute = Attribute::create([
            'company_id' => $companyId,
            'name' => $request->name,
        ]);

        foreach ($request->values as $val) {
            if (trim($val) !== '') {
                $attribute->values()->create([
                    'value' => trim($val),
                ]);
            }
        }

        return response()->json($attribute->load('values'), 201);
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->current_company_id;
        $attribute = Attribute::where('company_id', $companyId)->where('id', $id)->firstOrFail();
        $attribute->values()->delete();
        $attribute->delete();
        return response()->json(['success' => true]);
    }
}
