<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description', 8000)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_equipments');
    }
};
