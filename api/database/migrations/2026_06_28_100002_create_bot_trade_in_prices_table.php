<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_trade_in_prices', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->index();
            $table->string('model')->index();
            $table->string('storage')->nullable()->index();
            $table->string('condition', 32)->default('working')->index();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['brand', 'model', 'storage', 'condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_trade_in_prices');
    }
};
