<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('country_id')->index();
            $table->string('timezone', 64)->nullable();

            // Канонические координаты (центр города)
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            $table->string('place_id')->unique();

            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->cascadeOnDelete();
        });

        // 👉 PostGIS geography(Point, 4326)
        DB::statement(
            "ALTER TABLE cities
             ADD COLUMN point geography(Point, 4326)"
        );

        // 👉 Заполняем point из lat / lng
        DB::statement(
            "UPDATE cities
             SET point = ST_SetSRID(ST_MakePoint(lng, lat), 4326)::geography"
        );

        // 👉 Spatial index для радиусных запросов
        DB::statement(
            "CREATE INDEX cities_point_gix
             ON cities USING GIST (point)"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
