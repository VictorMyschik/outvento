<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_activities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('travel_id')->index();
            $table->unsignedBigInteger('activity')->index();

            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_activities');
    }
};
