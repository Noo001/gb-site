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
        Schema::create('bonus_terms', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50);
            $table->text('content');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_terms');
    }
};
