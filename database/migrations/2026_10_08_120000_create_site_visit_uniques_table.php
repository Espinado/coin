<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visit_uniques', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            $table->string('ip_hash', 64);
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['visit_date', 'ip_hash'], 'site_visit_uniques_date_ip_uq');
            $table->index('visit_date', 'site_visit_uniques_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visit_uniques');
    }
};
