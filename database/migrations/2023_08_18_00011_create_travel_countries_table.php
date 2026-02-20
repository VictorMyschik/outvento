<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_countries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('travel_id')->index();
            $table->unsignedBigInteger('country_id')->index();
            $table->smallInteger('sort');

            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();
            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_countries');
    }
};
