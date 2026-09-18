<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('channel', 16)->default('email');
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('registered_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->unique(['referrer_user_id', 'email']);
            $table->index(['referrer_user_id', 'registered_at']);
        });

        if (! Schema::hasColumn('users', 'referred_by_user_id')) {
            return;
        }

        $rows = DB::table('users')
            ->whereNotNull('referred_by_user_id')
            ->select(['id', 'email', 'referred_by_user_id', 'created_at'])
            ->get();

        foreach ($rows as $row) {
            DB::table('referral_invitations')->insertOrIgnore([
                'referrer_user_id' => $row->referred_by_user_id,
                'email' => strtolower((string) $row->email),
                'channel' => 'link',
                'sent_at' => $row->created_at,
                'registered_user_id' => $row->id,
                'registered_at' => $row->created_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_invitations');
    }
};
