<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id')->unique();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('name');
            $table->string('brand')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('subcategory')->nullable()->index();
            $table->decimal('price', 12, 2)->index();
            $table->decimal('old_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('RUB');
            $table->string('availability', 16)->default('out_of_stock')->index(); // in_stock | out_of_stock
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('url')->nullable();
            $table->string('image_url')->nullable();
            $table->json('available_in_cities')->nullable();
            $table->json('city_availability')->nullable();
            $table->json('metadata')->nullable();
            $table->text('search_text')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('updated_at')->nullable();

            $table->index(['is_active', 'availability']);
            $table->index(['is_active', 'brand']);
            $table->index(['is_active', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_products');
    }
};
