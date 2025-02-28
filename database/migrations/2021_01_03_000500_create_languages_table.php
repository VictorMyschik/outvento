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
            $table->string('ru')->nullable();
            $table->string('en')->nullable();
            $table->string('pl')->nullable();

            $table->timestampTz('created_at')->useCurrent();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translates');
    }
};
