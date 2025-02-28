<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_ru', 50);
            $table->string('name_en', 50)->nullable();
            $table->string('name_pl', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_types');
    }
};
