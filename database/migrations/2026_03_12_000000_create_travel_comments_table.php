<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_comments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('travel_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('parent_id')->index()->nullable();
            $table->text('content');
            $table->unsignedInteger('depth')->default(0);
            $table->integer('score')->default(0);
            $table->boolean('is_edited')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->unsignedInteger('replies_count')->default(0); // ▼ 12 replies

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('travel_comments')->cascadeOnDelete();

            $table->index(['travel_id', 'score']);
            $table->index(['travel_id', 'created_at']);
        });

        // ltree
        DB::statement('CREATE EXTENSION IF NOT EXISTS ltree');

        DB::statement('ALTER TABLE travel_comments ADD COLUMN path ltree');

        DB::statement('CREATE INDEX travel_comments_path_idx ON travel_comments USING GIST (path)');
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_comments');
    }
};
