<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_products', function (Blueprint $table) {
            $table->string('model_line')->nullable()->index()->after('subcategory');
            $table->string('storage')->nullable()->index()->after('model_line');
            $table->string('color')->nullable()->index()->after('storage');
            $table->string('sim')->nullable()->index()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('bot_products', function (Blueprint $table) {
            $table->dropColumn(['model_line', 'storage', 'color', 'sim']);
        });
    }
};
