<?php

use Database\Seeders\LegalPageSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        (new LegalPageSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
