<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_message_user_state', function (Blueprint $table) {
            $table->ulid('message_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary(['message_id', 'user_id']);
            $table->index(['user_id', 'message_id']);
            $table->foreign('message_id')->references('id')->on('conversation_messages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_message_user_state');
    }
};
