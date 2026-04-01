<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_message_links', function (Blueprint $table) {
            $table->id();

            $table->ulid('message_id')->index();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('url', 2048);
            $table->string('host', 255)->index();

            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('message_id')->references('id')->on('conversation_messages')->cascadeOnDelete();
            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['message_id', 'url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_message_links');
    }
};
