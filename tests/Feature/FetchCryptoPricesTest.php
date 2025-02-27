<?php

namespace Tests\Feature;

use App\Jobs\FetchCryptoPrices;
use App\Models\CryptoPair;
use App\Models\Exchange;
use App\Models\PriceUpdate;
use App\Events\CryptoPriceUpdated;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FetchCryptoPricesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate'); // Ensure database tables are created

        // Create mock crypto pairs and exchanges
        CryptoPair::create(['symbol' => 'BTC/USD']);
        Exchange::create(['name' => 'Binance']);
    }


    /** @test */
    public function it_fetches_crypto_prices_and_stores_them()
    {
        Event::fake(); // Prevent actual event broadcasting

        // Mock API response
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'symbols' => [
                    [
                        'symbol' => 'BTC/USD',
                        'last' => 45000.00,
                        'daily_change_percentage' => 2.5
                    ]
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $cryptoPair = CryptoPair::where('symbol', 'BTC/USD')->first();
        $exchange = Exchange::where('name', 'Binance')->first();
        // We inject mock client into job
        $job = new FetchCryptoPrices();
        $job->handle(); // Run the job

        dump(PriceUpdate::all());

        // Assertions
        $this->assertDatabaseHas('price_updates', [
            'crypto_pair_id' => $cryptoPair->id,
            'exchange_id' => $exchange->id,
            'price' => 45000.00000000,
            'change_percentage' => 2.5000
        ]);


        // We check if event was broadcasted
        Event::assertDispatched(CryptoPriceUpdated::class);
    }

    /** @test */
    public function it_handles_api_failure_gracefully()
    {
        Log::shouldReceive('error')->once();

        // Mock API failure response
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Inject mock client into job
        $job = new FetchCryptoPrices($client);
        $job->handle();


        // This ensure no prices are saved
        $this->assertDatabaseMissing('price_updates', []);
    }
}
