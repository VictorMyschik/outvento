<?php

declare(strict_types=1);

namespace App\Services\Conversations\Exceptions;

use App\Exceptions\ExceptionAPIBase;
use Illuminate\Http\Response;

final class ConversationMessageNotFoundException extends ExceptionAPIBase
{
    protected $message = 'Message not found';
    protected $code = Response::HTTP_NOT_FOUND;

    public function __construct(string $messageId)
    {
        parent::__construct("Message {$messageId} not found", $this->code);
    }
}