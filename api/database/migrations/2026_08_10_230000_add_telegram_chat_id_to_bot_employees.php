<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_employees', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('b24_token')->index();
        });
    }

    public function down(): void
    {
        Schema::table('bot_employees', function (Blueprint $table) {
            $table->dropColumn('telegram_chat_id');
        });
    }
};
