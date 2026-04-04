<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_pinned_messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('conversation_id')->index();
            $table->ulid('message_id')->index();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['conversation_id', 'message_id']);

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('message_id')->references('id')->on('conversation_messages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_pinned_messages');
    }
};
