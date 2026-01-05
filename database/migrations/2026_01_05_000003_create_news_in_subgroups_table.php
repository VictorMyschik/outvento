<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_in_subgroups', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('news_id')->index();
            $table->unsignedBigInteger('subgroup_id')->index();

            $table->foreign('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->foreign('subgroup_id')->references('id')->on('news_subgroups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_in_subgroups');
    }
};
