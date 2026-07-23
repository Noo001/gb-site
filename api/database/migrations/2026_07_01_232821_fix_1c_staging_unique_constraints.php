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
        foreach (['1c_categories', '1c_products', '1c_offers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['external_id']);
                $table->unique(['batch_id', 'external_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['1c_categories', '1c_products', '1c_offers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['batch_id', 'external_id']);
                $table->unique(['external_id']);
            });
        }
    }
};
