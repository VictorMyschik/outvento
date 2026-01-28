<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 32)->index();
            $table->boolean('active')->default(false)->index();
            $table->tinyInteger('language')->index();
            $table->text('text')->nullable();
            $table->timestampTz('published_at')->nullable()->index();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
