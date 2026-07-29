<?php

use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('type', 20)->default('store')->after('name');
        });

        foreach (Store::withTrashed()->cursor() as $store) {
            $store->type = Store::resolveType($store->name);
            $store->saveQuietly();
        }
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
