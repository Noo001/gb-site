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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->integer('bonus_balance')->default(0)->after('phone_verified_at');
            $table->unsignedSmallInteger('daily_streak_count')->default(0)->after('bonus_balance');
            $table->date('last_daily_bonus_at')->nullable()->after('daily_streak_count');
            $table->timestamp('accepted_bonus_terms_at')->nullable()->after('last_daily_bonus_at');
            $table->string('accepted_bonus_terms_version', 50)->nullable()->after('accepted_bonus_terms_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at',
                'bonus_balance',
                'daily_streak_count',
                'last_daily_bonus_at',
                'accepted_bonus_terms_at',
                'accepted_bonus_terms_version',
            ]);
        });
    }
};
