<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Message\MessageUpdateService;
use App\Services\SecureMail\SecureMailClient;
use App\Services\SecureMail\SecureMailException;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

use function sprintf;

class SyncMessageStatus extends Command
{
    /** @var string $signature */
    protected $signature = 'message:sync-status {--since=-1 week}';

    /** @var string $description */
    protected $description = 'Sync message status with the mailbox service';

    public function handle(
        MessageUpdateService $messageUpdateService,
        SecureMailClient $secureMailClient,
    ): int {
        $sinceOption = $this->option('since');

        try {
            $since = CarbonImmutable::parse($sinceOption);
        } catch (Throwable) {
            $this->error(sprintf('Invalid time string: %s', $sinceOption));
            return self::FAILURE;
        }

        try {
            $secureMailStatusUpdateCollection = $secureMailClient->getSecureMailStatusUpdates($since);
        } catch (SecureMailException $requestException) {
            $this->error(sprintf('Request to secure-mail failed: %s', $requestException->getMessage()));
            return self::FAILURE;
        }

        $messageUpdates = $messageUpdateService->updateMessages($secureMailStatusUpdateCollection->secureMailStatusUpdates);

        $this->info(sprintf('Number of message updates: %d', $messageUpdates));

        return self::SUCCESS;
    }
}
