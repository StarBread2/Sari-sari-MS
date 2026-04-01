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
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $sale = Sale::create([
                'sale_date' => $validated['sale_date'],
                'total_amount' => $totalAmount,
            ]);

            foreach ($validated['items'] as $item) {
                $latestStock = StockMovement::where('product_id', $item['product_id'])
                    ->latest('id')
                    ->first();

                $availableStock = $latestStock ? $latestStock->remaining_quantity : 0;

                if ($availableStock < $item['quantity']) {
                    throw new \Exception('Insufficient stock for product ID ' . $item['product_id']);
                }

                $unitCost = $latestStock ? $latestStock->unit_cost : 0;
                $newRemaining = $availableStock - $item['quantity'];

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