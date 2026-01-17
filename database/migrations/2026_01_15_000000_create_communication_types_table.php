<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('communication_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('name_ru', 50);
            $table->string('name_en', 50);
            $table->string('name_pl', 50);
            $table->string('image_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_types');
    }
};
