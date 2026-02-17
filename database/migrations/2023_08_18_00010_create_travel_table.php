<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travels', function (Blueprint $table) {
            $table->id();
            //$table->smallInteger('language')->index();
            $table->string('title')->index();
            $table->string('preview', 350)->nullable()->index();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->index();
            $table->unsignedBigInteger('country_id')->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->date('date_from')->index();
            $table->date('date_to')->index();
            $table->smallInteger('members')->nullable();
            $table->smallInteger('members_exists')->default(0);
            $table->unsignedBigInteger('travel_type_id')->index();
            $table->string('public_id', 15)->nullable()->index();
            $table->tinyInteger('visible_type')->default(0)->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('travel_type_id')->references('id')->on('travel_types')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travels');
    }
};
