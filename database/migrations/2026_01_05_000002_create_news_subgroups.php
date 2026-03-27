<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_subgroups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->string('code')->index();
            $table->string('title');

            $table->unique(['group_id', 'code']);

            $table->foreign('group_id')->references('id')->on('news_groups')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_subgroups');
    }
};