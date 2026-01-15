<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('type_id'); // Тип: телефон, email, url...
            $table->string('address');
            $table->string('description', 8000)->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('type_id')->references('id')->on('communication_types')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
