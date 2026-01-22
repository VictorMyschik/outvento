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
            $table->tinyInteger('file_type'); // image, video, file etc.
            $table->tinyInteger('media_type')->index();
            $table->string('path');
            $table->string('alt')->nullable();

            $table->index(['news_id', 'media_type']);

            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_media');
    }
};
