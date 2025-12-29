<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translate_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translate_id')->index();
            $table->unsignedInteger('group')->index(); // Enum TranslateGroupEnum.php

            $table->foreign('translate_id')->references('id')->on('translates')->onDelete('cascade');
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translate_groups');
    }
};
