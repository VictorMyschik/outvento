<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_notification_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active')->default(false)->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('event_type')->index();
            $table->unsignedBigInteger('communication_id')->index();

            $table->unique(['user_id', 'event_type', 'communication_id']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('communication_id')->references('id')->on('communications')->cascadeOnDelete();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_settings');
    }
};
