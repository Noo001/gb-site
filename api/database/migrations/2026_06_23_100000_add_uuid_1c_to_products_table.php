<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('uuid_1c')->nullable()->unique()->after('external_id');
            $table->index('uuid_1c');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['uuid_1c']);
            $table->dropIndex(['uuid_1c']);
            $table->dropColumn('uuid_1c');
        });
    }
};
