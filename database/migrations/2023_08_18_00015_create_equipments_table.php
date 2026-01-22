<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description', 8000)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->foreign('category_id')->references('id')->on('category_equipments')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
