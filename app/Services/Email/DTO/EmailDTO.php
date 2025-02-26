<?php

namespace App\Services\Email\DTO;

use Illuminate\Mail\Mailable;

final readonly class EmailDTO
{
    public function __construct(
        public string $to,
        public Mailable $mail,
    ) {}
}