<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active')->default(false);
            $table->boolean('public')->default(true);
            $table->smallInteger('language')->index();
            $table->unsignedBigInteger('group_id')->index();
            $table->string('code')->index();
            $table->string('title', 1000)->index();
            $table->text('text')->nullable();
            $table->timestampTz('published_at')->nullable()->index();

            $table->foreign('group_id')->references('id')->on('news_groups')->onDelete('cascade');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
