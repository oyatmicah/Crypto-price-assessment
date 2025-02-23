<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PriceUpdate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CryptoPrices extends Component
{
    public $prices = [];
    public $currentTime;
    protected $listeners = ['updatePrices' => 'fetchPrices'];

    public function mount()
    {
        $this->fetchPrices();
        $this->updateTime();
    }

    public function fetchPrices()
    {
        $prices = PriceUpdate::with(['cryptoPair', 'exchange'])
            ->latest()
            ->get();

        Log::info('Fetched Prices:', $prices->toArray());

        $grouped = $prices->groupBy('crypto_pair_id')
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

        Log::info('Grouped Prices:', $grouped);

        $this->prices = $grouped;
    }

    // public function updatePrices()
    // {
    //     $this->fetchPrices();
    // }
    public function updatePrices($newPrices)
    {
        $updatedPairs = [];

        foreach ($newPrices as $index => $newPrice) {
            if (isset($this->prices[$index]) && $this->prices[$index]['average_price'] != $newPrice['average_price']) {
                $updatedPairs[] = $newPrice['pair'];
            }
        }

        $this->prices = $newPrices;

        $this->dispatchBrowserEvent('highlightPrices', ['pairs' => $updatedPairs]);
    }

    public function updateTime()
    {
        $this->currentTime = now()->timezone(auth()->user()?->timezone ?? 'UTC')->format('H:i:s');
    }

    public function render()
    {
        return view('livewire.crypto-prices', ['prices' => $this->prices])->layout('layouts.app');
    }
}
