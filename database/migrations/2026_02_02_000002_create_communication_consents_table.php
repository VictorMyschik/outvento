<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('communication_consents', function (Blueprint $table): void {
            $table->id();
            //user_id (nullable)
            //recipient            -- email / phone / push_id
            //channel              -- EMAIL | SMS | PUSH
            //purpose              -- NEWS | PROMO | PRODUCT | PARTNER
            //status               -- PENDING | CONFIRMED | REVOKED
            //optin_at
            //optin_ip
            //confirmed_at
            //confirmed_ip
            //revoked_at
            //revoked_ip
            //source
            //consent_text_code
            //created_at
            //updated_at
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('recipient');
            $table->string('channel');
            $table->string('event');
            $table->string('status');

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_consents');
    }
};
