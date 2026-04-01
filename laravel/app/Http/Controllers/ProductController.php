<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'category',
            'prices',
            'currentPrice',
            'reorderLevel',
        ])->get();

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with([
            'category',
            'prices',
            'currentPrice',
            'purchaseItems',
            'saleItems',
            'stockMovements',
            'reorderLevel',
        ])->findOrFail($id);

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|max:50|unique:products,product_code',
            'product_name' => 'required|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:50',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'product_code' => 'required|string|max:50|unique:products,product_code,' . $id,
            'product_name' => 'required|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:50',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}