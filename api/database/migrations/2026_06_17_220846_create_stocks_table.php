<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('reserved', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['offer_id', 'store_id']);
            $table->index(['store_id', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
