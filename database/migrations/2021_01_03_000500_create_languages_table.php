<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('ru', 1000)->nullable();
            $table->string('en', 1000)->nullable();
            $table->string('pl', 1000)->nullable();

            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translates');
    }
};
