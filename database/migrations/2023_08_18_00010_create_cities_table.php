<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->index();
            $table->string('name_ru')->nullable()->index();
            $table->string('name_en')->nullable()->index();
            $table->string('name_pl')->nullable()->index();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
