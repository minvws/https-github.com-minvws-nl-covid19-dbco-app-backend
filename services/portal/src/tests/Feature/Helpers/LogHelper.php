<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use Monolog\Handler\TestHandler;
use Monolog\LogRecord;

use function app;
use function strpos;

class LogHelper
{
    public static function getMonologTestHandler(): TestHandler
    {
        // Retrieve the records from the Monolog TestHandler
        /** @var TestHandler $testLoggerHandler */
        $testLoggerHandler = app('log')->getHandlers()[0];
        return $testLoggerHandler;
    }

    public static function getAuditLogJson(): ?string
    {
        $testLoggerHandler = self::getMonologTestHandler();

        /** @var LogRecord $logRecord */
        foreach ($testLoggerHandler->getRecords() as $logRecord) {
            if (strpos($logRecord->message, "AuditEvent") !== false) {
                return $logRecord->message;
            }
        }

        return null;
    }
}
