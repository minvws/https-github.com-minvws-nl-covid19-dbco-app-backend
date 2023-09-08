<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OpenAPI\OpenAPIValidationFailedEvent;
use Psr\Log\LoggerInterface;

class OpenAPIValidationFailedEventLogger
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(
        OpenAPIValidationFailedEvent $event,
    ): void {
        $this->logger->info('OpenAPI validation failed', [
            'exception' => $event->exception->getMessage(),
            'operationPath' => $event->operation->path(),
            'operationMethod' => $event->operation->method(),
            'operationHasPlaceholders' => $event->operation->hasPlaceholders(),
        ]);
    }
}
