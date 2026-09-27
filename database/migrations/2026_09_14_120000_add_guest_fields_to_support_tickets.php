<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite cannot ALTER COLUMN to nullable; rebuild so guest tickets (null user_id) work in tests.
            Schema::disableForeignKeyConstraints();

            // Index names are global in SQLite — drop before rename to avoid collisions on recreate.
            $indexes = DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'support_tickets' AND name NOT LIKE 'sqlite_%'"
            );
            foreach ($indexes as $index) {
                DB::statement('DROP INDEX IF EXISTS "'.$index->name.'"');
            }

            Schema::rename('support_tickets', 'support_tickets_old');

            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->string('reference')->unique();
                $table->string('subject');
                $table->string('category');
                $table->string('status')->default('open');
                $table->timestamp('last_reply_at')->nullable();
                if (Schema::hasColumn('support_tickets_old', 'user_last_read_at')) {
                    $table->timestamp('user_last_read_at')->nullable();
                }
                if (Schema::hasColumn('support_tickets_old', 'admin_last_read_at')) {
                    $table->timestamp('admin_last_read_at')->nullable();
                }
                $table->string('guest_email')->nullable();
                $table->string('guest_token', 64)->nullable()->unique();
                $table->timestamps();
                $table->index(['status', 'updated_at']);
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });

            $oldColumns = collect(Schema::getColumnListing('support_tickets_old'));
            $newColumns = collect(Schema::getColumnListing('support_tickets'))
                ->reject(fn (string $column) => in_array($column, ['guest_email', 'guest_token'], true));
            $copyColumns = $oldColumns->intersect($newColumns)->values()->all();
            $columnList = implode(', ', array_map(fn (string $c) => '"'.$c.'"', $copyColumns));

            if ($columnList !== '') {
                DB::statement("INSERT INTO support_tickets ({$columnList}) SELECT {$columnList} FROM support_tickets_old");
            }

            Schema::drop('support_tickets_old');

            // Messages FK still pointed at the renamed table — rebuild so it targets support_tickets again.
            $messageIndexes = DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'support_ticket_messages' AND name NOT LIKE 'sqlite_%'"
            );
            foreach ($messageIndexes as $index) {
                DB::statement('DROP INDEX IF EXISTS "'.$index->name.'"');
            }

            Schema::rename('support_ticket_messages', 'support_ticket_messages_old');

            Schema::create('support_ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
                $table->string('author_type');
                $table->unsignedBigInteger('author_id');
                $table->text('body');
                $table->timestamps();
                $table->index(['support_ticket_id', 'created_at']);
            });

            $oldMessageColumns = collect(Schema::getColumnListing('support_ticket_messages_old'));
            $newMessageColumns = collect(Schema::getColumnListing('support_ticket_messages'));
            $copyMessageColumns = $oldMessageColumns->intersect($newMessageColumns)->values()->all();
            $messageColumnList = implode(', ', array_map(fn (string $c) => '"'.$c.'"', $copyMessageColumns));

            if ($messageColumnList !== '') {
                DB::statement(
                    "INSERT INTO support_ticket_messages ({$messageColumnList}) SELECT {$messageColumnList} FROM support_ticket_messages_old"
                );
            }

            Schema::drop('support_ticket_messages_old');
            Schema::enableForeignKeyConstraints();

            return;
        }

        $this->dropUserForeignKeys();

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('support_tickets', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('support_tickets', 'guest_token')) {
                $table->string('guest_token', 64)->nullable()->unique()->after('guest_email');
            }

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (Schema::hasColumn('support_tickets', 'guest_token')) {
                    $table->dropColumn('guest_token');
                }

                if (Schema::hasColumn('support_tickets', 'guest_email')) {
                    $table->dropColumn('guest_email');
                }
            });

            return;
        }

        $this->dropUserForeignKeys();

        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'guest_token')) {
                $table->dropColumn('guest_token');
            }

            if (Schema::hasColumn('support_tickets', 'guest_email')) {
                $table->dropColumn('guest_email');
            }
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    private function dropUserForeignKeys(): void
    {
        $foreignKeys = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'support_tickets'
               AND COLUMN_NAME = 'user_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        foreach ($foreignKeys as $foreignKey) {
            DB::statement(sprintf(
                'ALTER TABLE support_tickets DROP FOREIGN KEY `%s`',
                $foreignKey->CONSTRAINT_NAME,
            ));
        }
    }
};
