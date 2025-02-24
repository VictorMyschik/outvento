<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2);
            $table->string('name', 50);
            $table->boolean('active')->default(0);
        });

        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedSmallInteger('language_id');
            $table->string('translate');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('language_id')->references('id')->on('languages')->cascadeOnDelete();
            $table->unique(['code', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translates');
        Schema::dropIfExists('languages');
    }
};
