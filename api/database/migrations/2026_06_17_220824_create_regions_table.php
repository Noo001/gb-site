<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('default_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('prices_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('stocks_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
