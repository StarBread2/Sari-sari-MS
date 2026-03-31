<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        return response()->json(Purchase::all());
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => now(),
                'total_amount' => $request->total_amount,
            ]);

            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                ]);

                $product = Product::findOrFail($item['product_id']);
                $product->stock += $item['quantity'];
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $item['quantity'],
                    'reference' => 'Purchase #' . $purchase->id,
                ]);
            }

            DB::commit();

            return response()->json($purchase, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}