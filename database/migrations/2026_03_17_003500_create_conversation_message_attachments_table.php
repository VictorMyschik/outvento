<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->ulid('conversation_message_id')->index();
            $table->string('path');
            $table->string('name');
            $table->string('hash', 32)->index();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            // Denormalization for faster queries
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('conversation_id')->index();

            $table->timestampTz('created_at')->useCurrent();

            $table->index(['user_id', 'conversation_id']);
            $table->index(['conversation_id', 'hash']);

            $table->foreign('conversation_message_id')->references('id')->on('conversation_messages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_message_attachments');
    }
};
