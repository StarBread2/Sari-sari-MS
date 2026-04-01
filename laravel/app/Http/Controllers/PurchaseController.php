<?php

namespace App\Http\Controllers;

use App\Models\ProductPrice;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'items.product'])->get();

        return response()->json($purchases);
    }

    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product'])->findOrFail($id);

        return response()->json($purchase);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference_no' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $totalAmount,
                'reference_no' => $validated['reference_no'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'selling_price' => $item['selling_price'] ?? null,
                ]);

                $lastMovement = StockMovement::where('product_id', $item['product_id'])
                    ->latest('id')
                    ->first();

                $previousRemaining = $lastMovement ? $lastMovement->remaining_quantity : 0;
                $newRemaining = $previousRemaining + $item['quantity'];

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'movement_type' => 'PURCHASE',
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'remaining_quantity' => $newRemaining,
                    'purchase_item_id' => $purchaseItem->id,
                    'sale_item_id' => null,
                ]);

                if (!empty($item['selling_price'])) {
                    ProductPrice::create([
                        'product_id' => $item['product_id'],
                        'selling_price' => $item['selling_price'],
                        'effective_to' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json($purchase->load(['supplier', 'items.product']), 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create purchase',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}