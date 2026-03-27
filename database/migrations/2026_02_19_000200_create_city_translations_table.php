<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('city_translations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->smallInteger('language');
            $table->string('name');

            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_translations');
    }
};
