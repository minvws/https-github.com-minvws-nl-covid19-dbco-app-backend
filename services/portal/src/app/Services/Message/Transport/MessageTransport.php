<?php

declare(strict_types=1);

namespace App\Services\Message\Transport;

use App\Exceptions\MessageException;
use App\Models\Eloquent\EloquentMessage;

interface MessageTransport
{
    /**
     * @throws MessageException
     */
    public function send(EloquentMessage $eloquentMessage): ?string;
}
