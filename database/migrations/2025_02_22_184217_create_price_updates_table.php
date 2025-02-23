<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('price_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crypto_pair_id')->constrained()->onDelete('cascade');
            $table->foreignId('exchange_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 16, 8);
            $table->decimal('change_percentage', 10, 4);
            $table->timestamp('retrieved_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_updates');
    }
};
