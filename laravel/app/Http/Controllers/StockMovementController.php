<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function index()
    {
        return response()->json(StockMovement::all());
    }
}