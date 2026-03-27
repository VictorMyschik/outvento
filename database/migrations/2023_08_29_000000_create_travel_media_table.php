<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_id')->index();
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->integer('sort')->default(0);
            $table->string('description')->nullable();
            $table->tinyInteger('media_type')->default(0); // MediaType
            $table->boolean('is_avatar')->default(0);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_media');
    }
};
