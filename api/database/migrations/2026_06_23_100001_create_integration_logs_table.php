<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('direction', 16)->index(); // in | out
            $table->string('system', 32)->default('1c');
            $table->string('endpoint');
            $table->string('method', 16);
            $table->json('payload')->nullable();
            $table->json('headers')->nullable();
            $table->json('response')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['direction', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
