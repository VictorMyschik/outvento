<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_event_types', function (Blueprint $table): void {
            $table->id();
            $table->string('category')->index();
            $table->string('code')->index();
            $table->string('title')->index();
            $table->string('description')->nullable()->index();
            $table->string('image_path')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_event_types');
    }
};
