<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telegram_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('type')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('user_tg');
            $table->string('message');

            $table->unique(['user_id', 'user_tg']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_logs');
    }
};
