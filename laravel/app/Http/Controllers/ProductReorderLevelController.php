<?php

namespace App\Http\Controllers;

use App\Models\ProductReorderLevel;
use Illuminate\Http\Request;

class ProductReorderLevelController extends Controller
{
    // GET /api/reorder-levels
    public function index()
    {
        return response()->json(
            ProductReorderLevel::with('product')->get()
        );
    }

    // GET /api/reorder-levels/{id}
    public function show($id)
    {
        $item = ProductReorderLevel::with('product')->findOrFail($id);

        return response()->json($item);
    }

    // POST /api/reorder-levels
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:product_reorder_levels,product_id',
            'min_stock' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:1',
        ]);

        $item = ProductReorderLevel::create($validated);

        return response()->json($item->load('product'), 201);
    }

    // PUT /api/reorder-levels/{id}
    public function update(Request $request, $id)
    {
        $item = ProductReorderLevel::findOrFail($id);

        $validated = $request->validate([
            'min_stock' => 'sometimes|integer|min:0',
            'reorder_quantity' => 'sometimes|integer|min:1',
        ]);

        $item->update($validated);

        return response()->json($item->load('product'));
    }

    // DELETE /api/reorder-levels/{id}
    public function destroy($id)
    {
        $item = ProductReorderLevel::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    // GET /api/reorder-levels/low-stock
    public function lowStock()
    {
        $results = ProductReorderLevel::with('product')
            ->get()
            ->map(function ($item) {

                $stock = \App\Models\StockMovement::where('product_id', $item->product_id)
                    ->selectRaw("
                        COALESCE(SUM(
                            CASE 
                                WHEN movement_type = 'PURCHASE' THEN quantity
                                WHEN movement_type = 'SALE' THEN -quantity
                                WHEN movement_type = 'ADJUSTMENT' THEN quantity
                            END
                        ), 0) as stock
                    ")
                    ->value('stock');

                return [
                    'product' => $item->product,
                    'current_stock' => $stock,
                    'min_stock' => $item->min_stock,
                    'reorder_quantity' => $item->reorder_quantity,
                    'needs_reorder' => $stock <= $item->min_stock,
                ];
            })
            ->filter(fn($item) => $item['needs_reorder'])
            ->values();

        return response()->json($results);
    }
}