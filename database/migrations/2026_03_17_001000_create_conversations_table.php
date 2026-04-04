<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index(); // private | group | public
            $table->string('title')->nullable()->index();
            $table->string('slug')->nullable()->unique();
            $table->string('join_policy', 20)->index();
            $table->string('visibility', 20)->default('private')->index(); // private | searchable | public
            $table->string('avatar')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
