<?php

use App\Services\System\Enum\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translates', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedSmallInteger('language')->default(Language::RU->value)->index();
            $table->string('translate');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['code', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translates');
    }
};
