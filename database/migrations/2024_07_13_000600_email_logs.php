<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('type')->index();
            $table->string('email')->index();
            $table->string('subject')->nullable();
            $table->jsonb('sl')->index()->nullable();
            $table->tinyInteger('status')->index();
            $table->string('error')->nullable();

            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
