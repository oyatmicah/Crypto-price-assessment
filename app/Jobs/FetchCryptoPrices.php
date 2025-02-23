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
        event(new CryptoPriceUpdated());
        $client = new Client();
        $pairs = CryptoPair::pluck('symbol')->toArray();
        $exchanges = Exchange::pluck('name')->toArray();

        foreach ($exchanges as $exchange) {
            $symbols = implode("+", $pairs);
            $url = "https://api.freecryptoapi.com/v1/getData?symbol={$symbols}@{$exchange}";

            try {
                $response = $client->get($url, [
                    'headers' => ['Authorization' => 'Bearer YOUR_ACCESS_TOKEN']
                ]);

                $data = json_decode($response->getBody(), true);

                foreach ($data['symbols'] as $symbolData) {
                    $pair = CryptoPair::where('symbol', $symbolData['symbol'])->first();
                    $exchangeModel = Exchange::where('name', $exchange)->first();

                    if ($pair && $exchangeModel) {
                        PriceUpdate::create([
                            'crypto_pair_id' => $pair->id,
                            'exchange_id' => $exchangeModel->id,
                            'price' => $symbolData['last'],
                            'change_percentage' => $symbolData['daily_change_percentage'],
                            'retrieved_at' => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error fetching data from {$exchange}: " . $e->getMessage());
            }
        }
    }
}
