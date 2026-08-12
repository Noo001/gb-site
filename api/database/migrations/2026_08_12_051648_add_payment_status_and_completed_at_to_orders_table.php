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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('pending')->after('status');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->timestamp('completed_at')->nullable()->after('paid_at');
            $table->decimal('bonus_discount', 12, 2)->default(0)->after('total');
            $table->decimal('bonus_earned', 12, 2)->default(0)->after('bonus_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_at', 'completed_at', 'bonus_discount', 'bonus_earned']);
        });
    }
};
