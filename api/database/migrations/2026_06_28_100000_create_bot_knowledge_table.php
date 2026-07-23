<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_knowledge', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->index(); // config | store | tradein
            $table->string('group', 64)->nullable()->index();
            $table->string('key', 128)->nullable()->index();
            $table->json('payload');
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['type', 'group', 'key']);
            $table->index(['type', 'is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_knowledge');
    }
};
