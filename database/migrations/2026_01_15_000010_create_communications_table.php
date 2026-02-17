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
            $table->smallInteger('type')->index(); // Тип: телефон, email, url...
            $table->string('address')->index();
            $table->string('address_ext')->nullable(); // Дополнительные данные для адреса, например, telegram id
            $table->string('description')->nullable();
            $table->smallInteger('visibility')->default(0)->index();

            $table->tinyInteger('verification_status')->default(0);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
