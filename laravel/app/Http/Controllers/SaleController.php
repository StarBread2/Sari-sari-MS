<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return response()->json(Sale::all());
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $sale = Sale::create([
                'sale_date' => now(),
                'total_amount' => $request->total_amount,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: " . $product->name);
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                $product->stock -= $item['quantity'];
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference' => 'Sale #' . $sale->id,
                ]);
            }

            DB::commit();

            return response()->json($sale, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}