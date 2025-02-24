<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_id')->index();
            $table->string('name', 50);
            $table->string('original_name');
            $table->unsignedBigInteger('size');
            $table->integer('sort')->default(0);
            $table->string('description', 50)->nullable();
            $table->string('hash', 50);
            $table->unsignedBigInteger('user_id')->index();
            $table->tinyInteger('type')->default(0);
            $table->string('group')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_images');
    }
};
