<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_comment_votes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('user_id')->index();

            $table->tinyInteger('vote');

            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('comment_id')->references('id')->on('travel_comments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['comment_id', 'user_id']);
        });

        DB::statement('ALTER TABLE travel_comment_votes ADD CONSTRAINT vote_check CHECK (vote IN (-1,1))');
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_comment_votes');
    }
};
