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
        Schema::create('roulette_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('label', 255);
            $table->string('type', 32); // bonus, free_spin, service, material, super
            $table->integer('value')->default(0); // bonus amount or quantity
            $table->integer('cost_bonus')->default(0); // for material/service redemption
            $table->integer('probability_weight')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->json('metadata')->nullable(); // e.g. linked product_id, service name
            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roulette_sectors');
    }
};
