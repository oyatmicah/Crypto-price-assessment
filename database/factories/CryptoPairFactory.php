<?php

namespace Database\Factories;

use App\Models\CryptoPair;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoPairFactory extends Factory
{
    protected $model = CryptoPair::class;

    public function definition()
    {
        return [
            'symbol' => strtoupper($this->faker->lexify('???/???')), // Example: BTC/USD
        ];
    }
}
