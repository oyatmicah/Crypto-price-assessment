<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PriceUpdate;
use App\Models\CryptoPair;
use App\Models\Exchange;

class PriceUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cryptoPair = CryptoPair::firstOrCreate(['symbol' => 'BTC/USD']);
        $exchange = Exchange::firstOrCreate(['name' => 'Binance']);

        PriceUpdate::create([
            'crypto_pair_id' => $cryptoPair->id,
            'exchange_id' => $exchange->id,
            'price' => 45000,
            'change_percentage' => 2.5,
        ]);
    }
}
