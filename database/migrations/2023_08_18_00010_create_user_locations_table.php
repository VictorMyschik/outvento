<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->unique();
            $table->foreignId('city_id')->index();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->unsignedSmallInteger('radius_km')->nullable();
            $table->boolean('is_visible')->default(true);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });

        DB::statement(" ALTER TABLE user_locations ADD COLUMN geom geography(Point, 4326)");
        DB::statement("CREATE INDEX user_locations_geom_idx ON user_locations USING GIST (geom)");
    }

    public function down(): void
    {
        Schema::dropIfExists('user_locations');
    }
};
