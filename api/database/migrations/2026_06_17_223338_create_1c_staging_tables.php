<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('1c_categories', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('parent_external_id')->nullable()->index();
            $table->string('name');
            $table->json('raw')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('1c_products', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('category_external_id')->nullable()->index();
            $table->string('name');
            $table->json('raw')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('1c_offers', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('product_external_id')->nullable()->index();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->json('raw')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('1c_prices', function (Blueprint $table) {
            $table->id();
            $table->string('offer_external_id')->index();
            $table->string('price_type')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->json('raw')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('1c_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('offer_external_id')->index();
            $table->string('store_external_id')->nullable()->index();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->json('raw')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('1c_stocks');
        Schema::dropIfExists('1c_prices');
        Schema::dropIfExists('1c_offers');
        Schema::dropIfExists('1c_products');
        Schema::dropIfExists('1c_categories');
    }
};
