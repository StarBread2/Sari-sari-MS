<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json(Supplier::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json($supplier, 201);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $supplier->update($validated);

        return response()->json($supplier);
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully'
        ]);
    }
}