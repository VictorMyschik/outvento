<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('terms_and_conditions', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active')->default(false);
            $table->tinyInteger('language')->index();
            $table->text('text')->nullable();
            $table->timestampTz('published_at')->nullable()->index();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_and_conditions');
    }
};
