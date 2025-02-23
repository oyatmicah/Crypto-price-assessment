<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceUpdate extends Model
{
    use HasFactory;

    protected $fillable = ['crypto_pair_id', 'exchange_id', 'price', 'change_percentage', 'retrieved_at'];

    protected $casts = [
        'retrieved_at' => 'datetime',
    ];

    public function cryptoPair()
    {
        return $this->belongsTo(CryptoPair::class);
    }

    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }
}
