<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('album_travels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_id')->index();
            $table->unsignedBigInteger('album_id')->index();

            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['album_id', 'travel_id']);

            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
            $table->foreign('album_id')->references('id')->on('albums')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('album_travels');
    }
};
