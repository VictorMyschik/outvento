<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_media', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('news_id')->index();
            $table->string('type', 50);
            $table->tinyInteger('media_type');
            $table->string('path');
            $table->string('alt')->nullable();

            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_media');
    }
};
