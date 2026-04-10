<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('album_media_likes', function (Blueprint $table): void {
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('user_id');
            $table->smallInteger('icon');
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary(['media_id', 'user_id']);

            $table->foreign('media_id')->references('id')->on('album_media')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('album_media_likes');
    }
};

