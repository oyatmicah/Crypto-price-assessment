<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoPair extends Model
{
    use HasFactory;

    protected $fillable = ['symbol'];

    public function priceUpdates()
    {
        return $this->hasMany(PriceUpdate::class);
    }
}
