<?php

use App\Services\System\Enum\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->index(); // NotificationType
            $table->string('email', 100)->index();
            $table->string('token', 32)->unique()->index();
            $table->tinyInteger('language')->default(Language::RU->value)->index();

            $table->unique(['email', 'type', 'language']);
            $table->index(['email', 'type', 'language']);

            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
