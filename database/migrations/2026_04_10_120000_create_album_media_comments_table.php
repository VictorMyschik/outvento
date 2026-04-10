<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('album_media_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('user_id');
            $table->string('body', 10000);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('media_id')->references('id')->on('album_media')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('album_media_comments');
    }
};

