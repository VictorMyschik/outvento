<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_groups', function (Blueprint $table): void {
            $table->id();
            $table->tinyInteger('language')->index(); // Language::RU
            $table->boolean('active')->default(false);
            $table->string('title');
            $table->string('code')->index();

            $table->unique(['language', 'code']);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_groups');
    }
};
