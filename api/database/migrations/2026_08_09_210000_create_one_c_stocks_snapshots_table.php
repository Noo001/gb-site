<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('one_c_stocks_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id', 64)->index();
            $table->string('offer_external_id');
            $table->string('store_external_id')->nullable();
            $table->string('store_name')->nullable();
            $table->decimal('quantity', 15, 2);
            $table->timestamps();

            $table->index(['batch_id', 'offer_external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('one_c_stocks_snapshots');
    }
};
