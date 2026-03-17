<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('conversation_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->ulid('last_read_message_id')->nullable();
            $table->timestamp('muted_until')->nullable();
            $table->timestamp('deleted_at')->nullable(); // soft delete per user
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(['conversation_id', 'user_id']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
