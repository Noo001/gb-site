<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('text')->comment('text, number, boolean, select');
            $table->string('unit')->nullable();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_filter')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
