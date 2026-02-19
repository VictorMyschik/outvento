<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('language_id')->index();
            $table->tinyInteger('level')->nullable();   // native | fluent | intermediate | basic

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('language_id')->references('id')->on('languages')->cascadeOnDelete();
            $table->unique(['user_id', 'language_id']);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_languages');
    }
};
