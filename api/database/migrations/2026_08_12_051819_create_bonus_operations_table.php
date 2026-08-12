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
        Schema::create('bonus_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32); // registration, daily, streak, purchase, spend, roulette, promo, redeem, reversal
            $table->integer('amount'); // may be negative for spend/reversal
            $table->integer('balance_after');
            $table->string('description', 500)->nullable();
            $table->json('payload')->nullable();
            $table->morphs('related'); // related order, spin, etc.
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_operations');
    }
};
