<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travels', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('language')->index();
            $table->string('title')->index();
            $table->string('preview', 350)->nullable()->index();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->index();
            $table->unsignedBigInteger('start_city_id')->nullable()->index();
            $table->date('date_from')->nullable()->index();
            $table->date('date_to')->nullable()->index();
            $table->smallInteger('members')->nullable();
            $table->smallInteger('members_exists')->default(0);
            $table->string('public_id', 15)->nullable()->index();
            $table->string('private_id', 64)->index();
            $table->tinyInteger('visible')->default(0)->index();

            $table->timestampTz('archived_at')->nullable()->index(); // Архивирование - для скрытия из общего списка, но сохранения данных
            $table->timestampTz('deleted_at')->nullable()->index(); // Удаление - для полного удаления из системы, но с возможностью восстановления (soft delete)

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();

            $table->foreign('start_city_id')->references('id')->on('cities')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travels');
    }
};
