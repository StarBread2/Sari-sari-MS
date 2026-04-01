<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('items.product')->get();

        return response()->json($sales);
    }

    public function show($id)
    {
        $sale = Sale::with('items.product')->findOrFail($id);

        return response()->json($sale);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // 🔥 STEP 1: Merge same products (VERY IMPORTANT)
            $groupedItems = [];

            foreach ($validated['items'] as $item) {
                $pid = $item['product_id'];

                if (!isset($groupedItems[$pid])) {
                    $groupedItems[$pid] = [
                        'product_id' => $pid,
                        'quantity' => 0,
                        'unit_price' => $item['unit_price'], // assume same price
                    ];
                }

                $groupedItems[$pid]['quantity'] += $item['quantity'];
            }

            // 🔥 STEP 2: Check stock using SUM (REAL STOCK)
            foreach ($groupedItems as $item) {
                $stock = StockMovement::where('product_id', $item['product_id'])
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

                if ($stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product ID {$item['product_id']}");
                }
            }

            // 🔥 STEP 3: Compute total
            $totalAmount = 0;
            foreach ($groupedItems as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // 🔥 STEP 4: Create sale
            $sale = Sale::create([
                'sale_date' => $validated['sale_date'],
                'total_amount' => $totalAmount,
            ]);

            // 🔥 STEP 5: Insert sale items + stock movements
            foreach ($groupedItems as $item) {

                // get latest cost (still not FIFO but ok for now)
                $latestStock = StockMovement::where('product_id', $item['product_id'])
                    ->latest('id')
                    ->first();

                $unitCost = $latestStock ? $latestStock->unit_cost : 0;

                // recompute stock again (for remaining_quantity)
                $currentStock = StockMovement::where('product_id', $item['product_id'])
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

                $newRemaining = $currentStock - $item['quantity'];

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $unitCost,
                ]);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'movement_type' => 'SALE',
                    'quantity' => $item['quantity'],
                    'unit_cost' => $unitCost,
                    'remaining_quantity' => $newRemaining,
                    'purchase_item_id' => null,
                    'sale_item_id' => $saleItem->id,
                ]);
            }

            DB::commit();

            return response()->json($sale->load('items.product'), 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create sale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}