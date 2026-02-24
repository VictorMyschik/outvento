<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_points', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('travel_id')->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();

            $table->smallInteger('type')->index();      // start / finish / stop / poi
            $table->integer('position')->nullable();    // порядок маршрута
            $table->smallInteger('rating')->default(0);    // пользовательская оценка

            $table->string('address')->nullable();      // Google formatted_address
            $table->text('description')->nullable();    // пользовательское примечание

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });

        // PostGIS point
        DB::statement(
            "ALTER TABLE travel_points
             ADD COLUMN point geography(Point, 4326) NOT NULL"
        );

        // Spatial index
        DB::statement(
            "CREATE INDEX travel_points_point_gix
             ON travel_points USING GIST (point)"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_points');
    }
};
