<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_message_id')->constrained('support_ticket_messages')->cascadeOnDelete();
            $table->string('disk', 32)->default('local');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->foreignId('kyc_document_id')->nullable()->constrained('kyc_documents')->nullOnDelete();
            $table->timestamps();

            $table->index(['support_ticket_message_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_message_attachments');
    }
};
