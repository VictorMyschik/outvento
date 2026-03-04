<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->tinyInteger('type'); // TravelResource
            $table->string('title')->nullable();
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('sort')->default(0);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('travel_id')->references('id')->on('travels')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['travel_id', 'sort']);
            $table->index(['travel_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_resources');
    }
};
