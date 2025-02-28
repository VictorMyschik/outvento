<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('name_en', 50)->nullable();
            $table->string('name_pl', 50)->nullable();
            $table->char('iso3166alpha2', 3);
            $table->char('iso3166alpha3', 4);
            $table->char('iso3166numeric', 3);
            $table->tinyInteger('continent');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
