<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE deposits MODIFY amount DECIMAL(16, 8) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE deposits MODIFY amount DECIMAL(14, 2) NOT NULL');
    }
};
