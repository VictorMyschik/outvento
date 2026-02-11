<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('model_roles', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->index();
            $table->unsignedBigInteger('model_id')->index();
            $table->unsignedBigInteger('role_id')->index();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_roles');
    }
};
