<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_invitations', function (Blueprint $table) {
            $table->string('role', 32)->default('operator')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('admin_invitations', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
