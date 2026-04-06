<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('visibility', 20)->default('private')->index();
            $table->string('avatar')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
