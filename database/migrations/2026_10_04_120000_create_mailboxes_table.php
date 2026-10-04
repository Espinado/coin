<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('local_part');
            $table->text('password')->nullable();
            $table->unsignedInteger('quota_mb')->nullable();
            $table->timestamps();

            $table->index('local_part');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailboxes');
    }
};
