<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id', 128)->nullable()->index();
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'product_id', 'offer_id'], 'cart_user_product_offer_unique');
            $table->unique(['session_id', 'product_id', 'offer_id'], 'cart_session_product_offer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
