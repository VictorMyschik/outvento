<?php

declare(strict_types=1);

namespace App\Models\Promo;

use App\Models\ORM\ORM;
use App\Models\Other\LegalDocument;

class SubscriptionLegalAcceptance extends ORM
{
    protected $table = 'subscription_legal_acceptances';

    protected array $allowedSorts = [
        'id',
        'subscription_id',
        'legal_document_id',
        'accepted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function getSubscription(): Subscription
    {
        return Subscription::loadByOrDie($this->subscription_id);
    }

    public function legalDocument(): LegalDocument
    {
        return LegalDocument::loadByOrDie($this->legal_document_id);
    }

}