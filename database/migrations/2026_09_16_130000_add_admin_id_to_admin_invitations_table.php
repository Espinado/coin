<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_invitations', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->nullable()
                ->after('name')
                ->constrained('admins')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admin_invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });
    }
};
