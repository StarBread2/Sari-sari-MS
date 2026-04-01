<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with([
            'product',
            'purchaseItem',
            'saleItem',
        ])->get();

        return response()->json($movements);
    }

    public function show($id)
    {
        $movement = StockMovement::with([
            'product',
            'purchaseItem',
            'saleItem',
        ])->findOrFail($id);

        return response()->json($movement);
    }
}