<?php

namespace App\Services;

use App\Models\PriceUpdate;
use Carbon\Carbon;

class CryptoPriceService
{
    public function getFormattedPrices()
    {
        $prices = PriceUpdate::with(['cryptoPair', 'exchange'])
            ->latest()
            ->get();

        return $prices->groupBy('crypto_pair_id')
            ->map(function ($updates) {
                $first = $updates->first();
                return [
                    'pair' => optional($first->cryptoPair)->symbol,
                    'average_price' => round($updates->avg('price'), 2),
                    'change_percentage' => $first->change_percentage,
                    'last_updated' => Carbon::parse($first->retrieved_at)->diffForHumans(),
                    'exchanges' => $updates->pluck('exchange.name')->implode(', ')
                ];
            })->values()->toArray();
    }
}
