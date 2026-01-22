<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active')->default(false);// reed/unreed
            $table->unsignedSmallInteger('type')->index();
            $table->tinyInteger('language')->default(0)->index();
            $table->jsonb('sl')->index()->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
