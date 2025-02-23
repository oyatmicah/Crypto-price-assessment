<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceUpdate;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    public function getPrices()
    {
        return response()->json(PriceUpdate::with(['cryptoPair', 'exchange'])->latest()->get());
    }
}
