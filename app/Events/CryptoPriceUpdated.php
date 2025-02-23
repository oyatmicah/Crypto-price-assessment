<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\PriceUpdate;
use Carbon\Carbon;

class CryptoPriceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prices;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->prices = PriceUpdate::with(['cryptoPair', 'exchange'])
            ->latest()
            ->get()
            ->groupBy('crypto_pair_id')
            ->map(function ($updates) {
                $first = $updates->first();
                return [
                    'pair' => $first->cryptoPair->symbol,
                    'average_price' => round($updates->avg('price'), 2),
                    'change_percentage' => $first->change_percentage,
                    'last_updated' => Carbon::parse($update->retrieved_at)->timezone('Africa/Lagos')->diffForHumans(),
                    // 'last_updated' => Carbon::parse($first->retrieved_at)->diffForHumans(),
                    'exchanges' => $updates->pluck('exchange.name')->implode(', ')
                ];
            })->values();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('crypto-prices');
    }

    public function broadcastAs()
    {
        return 'price.updated';
    }
}
