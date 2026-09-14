<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('guest_email')->nullable()->after('user_id');
            $table->string('guest_token', 64)->nullable()->unique()->after('guest_email');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['guest_email', 'guest_token']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
