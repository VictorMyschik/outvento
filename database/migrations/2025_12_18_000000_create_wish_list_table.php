<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table): void {
            $table->id();
            $table->string('category')->index();
            $table->jsonb('subcategory')->index()->nullable();
            $table->string('title')->index();
            $table->string('url')->nullable()->index();
            $table->decimal('price', 12, 4)->nullable()->index();
            $table->string('currency')->nullable();

            $table->unsignedBigInteger('user_id')->index();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            $table->timestampTz('archived_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
