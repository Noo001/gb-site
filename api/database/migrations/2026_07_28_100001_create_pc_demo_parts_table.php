<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pc_demo_parts', function (Blueprint $table) {
            $table->id();
            $table->string('slot');
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->default(1);
            $table->json('attributes')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_demo_parts');
    }
};
