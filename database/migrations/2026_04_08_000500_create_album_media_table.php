<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('album_media', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('album_id')->index();
            $table->tinyInteger('file_type'); // image, video
            $table->string('mime', 50)->index();
            $table->unsignedBigInteger('size')->index();
            $table->string('path');
            $table->string('original_name');
            $table->integer('sort')->default(0);
            $table->string('hash', 32)->index();
            $table->string('description')->nullable();
            $table->string('address')->nullable();

            $table->index(['album_id', 'hash']);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });

        // PostGIS point
        DB::statement(
            "ALTER TABLE album_media
             ADD COLUMN point geography(Point, 4326)"
        );

        // Spatial index
        DB::statement(
            "CREATE INDEX album_medias_point_gix
             ON travel_points USING GIST (point)"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('album_media');
    }
};
