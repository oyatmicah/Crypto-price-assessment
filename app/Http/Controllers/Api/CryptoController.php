<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceUpdate;
use Illuminate\Http\Request;
use App\Services\CryptoPriceService;

class CryptoController extends Controller
{
    public function getPrices(CryptoPriceService $cryptoPriceService)
    {
        return response()->json($cryptoPriceService->getFormattedPrices());
    }
}
