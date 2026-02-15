<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->index();
            $table->smallInteger('language')->index();
            $table->string('event', 50)->index();
            $table->string('status', 20)->index();  // pending | confirmed | revoked
            $table->string('token', 64)->nullable()->unique();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            // GDPR audit
            $table->timestampTz('optin_at')->nullable();
            $table->string('optin_source', 50)->nullable(); // footer, profile, popup

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });

        DB::statement("
            CREATE UNIQUE INDEX subscriptions_active_unique
            ON subscriptions (email, event)
            WHERE status IN ('PENDING', 'CONFIRMED')
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
