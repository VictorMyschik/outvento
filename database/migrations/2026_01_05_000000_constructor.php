<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('constructors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('object_id')->index();
            $table->smallInteger('type')->index(); // ObjectEnum
            $table->string('title', 1000);
            $table->string('description', 10000)->nullable();
            $table->tinyInteger('language')->index();
            $table->unsignedInteger('sort')->default(0);
        });

        Schema::create('constructor_item_sliders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('constructor_id')->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->foreign('constructor_id')->references('id')->on('constructors')->onDelete('cascade');
        });

        Schema::create('constructor_item_slides', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('slider_id')->index();
            $table->string('display_name')->nullable();
            $table->string('file_name');
            $table->string('alt')->nullable();
            $table->string('path');
            $table->unsignedInteger('sort')->default(0);
            $table->foreign('slider_id')->references('id')->on('constructor_item_sliders')->onDelete('cascade');
        });

        Schema::create('constructor_item_texts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('constructor_id')->index();
            $table->unsignedInteger('sort')->default(0);
            $table->string('title')->nullable();
            $table->text('text')->nullable();

            $table->foreign('constructor_id')->references('id')->on('constructors')->onDelete('cascade');
        });

        Schema::create('constructor_item_videos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('constructor_id')->index();
            $table->string('title')->index()->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->unsignedBigInteger('file_id');

            $table->foreign('constructor_id')->references('id')->on('constructors')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('news_media')->onDelete('cascade');
        });

        Schema::create('constructor_item_out_videos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('constructor_id')->index();
            $table->string('title')->index()->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->string('url', 10000);

            $table->foreign('constructor_id')->references('id')->on('constructors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constructor_item_out_videos');
        Schema::dropIfExists('constructor_item_slides');
        Schema::dropIfExists('constructor_item_sliders');
        Schema::dropIfExists('constructor_item_videos');
        Schema::dropIfExists('constructor_item_texts');
        Schema::dropIfExists('constructors');
    }
};
