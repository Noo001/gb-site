<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['1c_categories', '1c_products', '1c_offers', '1c_prices', '1c_stocks'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedSmallInteger('attempts')->default(0)->after('processed_at');
                $table->text('error')->nullable()->after('attempts');
            });
        }
    }

    public function down(): void
    {
        foreach (['1c_categories', '1c_products', '1c_offers', '1c_prices', '1c_stocks'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['attempts', 'error']);
            });
        }
    }
};
