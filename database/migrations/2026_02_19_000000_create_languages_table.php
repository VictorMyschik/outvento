<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            // ISO 639-1 / 639-2 / BCP-47 base
            $table->string('code', 10)->unique(); // en, de, it, pt-BR
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
