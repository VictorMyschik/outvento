<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('parent_id')->nullable()->index();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->unsignedInteger('deleted_by_count')->default(0);
            $table->foreignId('user_id')->index();
            $table->string('content', 10000)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('edited_at')->nullable();

            $table->index(['conversation_id', 'id']);

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
    }
};
