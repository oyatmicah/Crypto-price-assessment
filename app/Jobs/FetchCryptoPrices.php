<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use App\Models\CryptoPair;
use App\Models\Exchange;
use App\Models\PriceUpdate;
use Illuminate\Support\Facades\Log;
use App\Events\CryptoPriceUpdated;

class FetchCryptoPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function __construct(protected ?Client $client = null)
    {

    }

    public function handle(): void
    {
        $client = $this->client ?? new Client();
        $pairs = CryptoPair::all()->keyBy('symbol');
        $exchanges = Exchange::all()->keyBy('name');

        $promises = [];
        $prices = [];

        foreach ($exchanges as $exchangeName => $exchange) {
            $symbols = implode("+", $pairs->keys()->toArray()); // Convert symbols to a proper string
            $url = "https://api.freecryptoapi.com/v1/getData?symbol={$symbols}@{$exchangeName}";

            // Create async request for each exchange
            $promises[$exchangeName] = $client->getAsync($url, [
                'headers' => ['Authorization' => 'Bearer ' . env('CRYPTO_API_KEY')]
            ])->then(
                function ($response) use ($exchangeName, $pairs, $exchange, &$prices) {
                    $data = json_decode($response->getBody(), true);

                    if (isset($data['symbols']) && is_array($data['symbols'])) {
                        foreach ($data['symbols'] as $symbolData) {
                            if (isset($pairs[$symbolData['symbol']])) {
                                PriceUpdate::create([
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
                    }
                },
                function ($exception) use ($exchangeName) {
                    Log::error("Error fetching data from {$exchangeName}: " . $exception->getMessage());
                }
            );
        }

        // Execute all API calls asynchronously
        Utils::settle($promises)->wait();

        if (!empty($prices)) {
            // Broadcast the updated prices
            event(new CryptoPriceUpdated($prices));
            Log::info('Broadcasting Crypto Prices', ['prices' => $prices]);
        }
    }
}
