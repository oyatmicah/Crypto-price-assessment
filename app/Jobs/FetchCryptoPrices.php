<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use App\Models\CryptoPair;
use App\Models\Exchange;
use App\Models\PriceUpdate;
use Illuminate\Support\Facades\Log;
use App\Events\CryptoPriceUpdated;

class FetchCryptoPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client();
        $pairs = CryptoPair::all()->keyBy('symbol'); 
        $exchanges = Exchange::all()->keyBy('name');

        $prices = [];

        foreach ($exchanges as $exchangeName => $exchange) {
            //we have to extract the symbols
            $symbols = implode("+", $pairs->keys()->toArray());

            $url = "https://api.freecryptoapi.com/v1/getData?symbol={$symbols}@{$exchangeName}";

            try {
                $response = $client->get($url, [
                    'headers' => ['Authorization' => 'Bearer ' . env('CRYPTO_API_KEY')]
                ]);

                $data = json_decode($response->getBody(), true);
                foreach ($data['symbols'] as $symbolData) {
                    if ($pairs->has($symbolData['symbol'])) {
                        $priceUpdate = PriceUpdate::create([
                            'crypto_pair_id' => $pairs[$symbolData['symbol']]->id,
                            'exchange_id' => $exchange->id,
                            'price' => $symbolData['last'],
                            'change_percentage' => $symbolData['daily_change_percentage'],
                            'retrieved_at' => now(),
                        ]);

                        // Collect prices for broadcasting
                        $prices[] = [
                            'symbol' => $symbolData['symbol'],
                            'price' => $symbolData['last'],
                            'change' => $symbolData['daily_change_percentage'],
                            'exchange' => $exchangeName,
                            'last_updated' => now()->toDateTimeString(),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error fetching data from {$exchangeName}: " . $e->getMessage());
            }
        }

        if (!empty($prices)) {
            // Broadcast the updated prices
            event(new CryptoPriceUpdated($prices));
            Log::info('Broadcasting Crypto Prices', ['prices' => $prices]);
        }
    }
}
