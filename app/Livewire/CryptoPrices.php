<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PriceUpdate;
use App\Services\CryptoPriceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CryptoPrices extends Component
{
    public $prices = [];
    public $currentTime;
    protected $listeners = ['updatePrices' => 'fetchPrices'];

    public function mount()
    {
        $this->updateTime();
    }

    public function fetchPrices()
    {
        $this->prices = app(CryptoPriceService::class)->getFormattedPrices();
    }

    public function updateTime()
    {
        $this->currentTime = now()->timezone(auth()->user()?->timezone ?? 'UTC')->format('H:i:s');
    }

    public function render()
    {
        return view('livewire.crypto-prices', [
            'prices' => $this->prices ?: app(CryptoPriceService::class)->getFormattedPrices()
        ])->layout('layouts.app');
    }
}
