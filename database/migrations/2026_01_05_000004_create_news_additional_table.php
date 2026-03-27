<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_additional', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('news_id')->index();
            $table->unsignedBigInteger('relation_object_id')->index();
            $table->tinyInteger('relation_object_type')->index();
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['news_id', 'relation_object_id', 'relation_object_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_additional');
    }
};
