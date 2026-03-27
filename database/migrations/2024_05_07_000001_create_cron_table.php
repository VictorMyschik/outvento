<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function down(): void
    {
        Schema::dropIfExists('cron');
    }

    public function up(): void
    {
        Schema::create('cron', function (Blueprint $table): void {
            $table->id();

            $table->boolean('active')->default(false);
            $table->unsignedBigInteger('period');
            $table->timestamp('last_work')->nullable();
            $table->string('description')->nullable();
            $table->string('cron_key', 50)->unique();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }
};
