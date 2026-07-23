<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_1c_exports', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->string('endpoint')->default('/api/1c/products');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['processed_at', 'failed_at', 'attempts']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_1c_exports');
    }
};
