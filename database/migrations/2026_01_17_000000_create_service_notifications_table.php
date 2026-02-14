<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_notifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->smallInteger('event')->index(); // ServiceNotificationType
            $table->unsignedBigInteger('communication_id')->index();
            $table->string('channel', 50)->index(); // NotificationChannel

            $table->unique(['user_id', 'event', 'communication_id']);
            $table->index(['user_id', 'event', 'channel']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('communication_id')->references('id')->on('communications')->cascadeOnDelete();

            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_notifications');
    }
};
