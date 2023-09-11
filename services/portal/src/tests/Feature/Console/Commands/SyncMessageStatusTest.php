<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Services\SecureMail\SecureMailClient;
use App\Services\SecureMail\SecureMailException;
use App\Services\SecureMail\SecureMailStatusUpdate;
use App\Services\SecureMail\SecureMailStatusUpdateCollection;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\MessageStatus;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class SyncMessageStatusTest extends FeatureTestCase
{
    public function testRunWithoutResults(): void
    {
        $this->mock(SecureMailClient::class, static function (MockInterface $mock): void {
            $mock->expects('getSecureMailStatusUpdates')
                ->once()
                ->andReturn(new SecureMailStatusUpdateCollection(0, 0, []));
        });

        $this->artisan('message:sync-status')
            ->assertSuccessful()
            ->expectsOutput('Number of message updates: 0');
    }

    public function testRunWithSingleResult(): void
    {
        $message = $this->createMessage();

        $this->mock(SecureMailClient::class, function (MockInterface $mock) use ($message): void {
            $mock->expects('getSecureMailStatusUpdates')
                ->once()
                ->andReturn(new SecureMailStatusUpdateCollection(1, 1, [
                    new SecureMailStatusUpdate(
                        $message->uuid,
                        $this->faker->boolean() ? CarbonImmutable::instance($this->faker->dateTimeBetween()) : null,
                        $this->faker->randomElement(MessageStatus::all()),
                    ),
                ]));
        });

        $this->artisan('message:sync-status')
            ->assertSuccessful()
            ->expectsOutput('Number of message updates: 1');
    }

    public function testRunWithFailingClient(): void
    {
        $errorMessage = $this->faker->sentence();

        $this->mock(SecureMailClient::class, static function (MockInterface $mock) use ($errorMessage): void {
            $mock->expects('getSecureMailStatusUpdates')
                ->once()
                ->andThrow(new SecureMailException($errorMessage));
        });

        $this->artisan('message:sync-status')
            ->assertFailed()
            ->expectsOutput(sprintf('Request to secure-mail failed: %s', $errorMessage));
    }

    public function testRunWithInvalidSince(): void
    {
        $invalidSince = $this->faker->word();
        $this->artisan(sprintf('message:sync-status --since=%s', $invalidSince))
            ->assertFailed()
            ->expectsOutput(sprintf('Invalid time string: %s', $invalidSince));
    }
}
