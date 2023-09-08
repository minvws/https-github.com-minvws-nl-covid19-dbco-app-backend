<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditUser;
use MinVWS\Audit\Repositories\AuditRepository;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function array_key_exists;
use function collect;

class MiscServiceProviderTest extends FeatureTestCase
{
    #[DataProvider('bcosyncAuditPropertySetDataProvider')]
    public function testBcosyncAuditPropertySet(array $headers): void
    {
        $user = $this->createUser();
        $auditRepository = $this->spy(AuditRepository::class);

        $response = $this->be($user)->get('/api/cases/mine', $headers);

        $this->assertAuditUserDetailSet($auditRepository);
        $response->assertStatus(200);
    }

    public static function bcosyncAuditPropertySetDataProvider(): array
    {
        return [
            [
                ['bcosync-version' => 5],
            ],
            [
                ['X-bcosync-version' => '123'],
            ],
        ];
    }

    public function testBcosyncAuditPropertyNotSet(): void
    {
        $user = $this->createUser();
        $auditRepository = $this->spy(AuditRepository::class);

        $response = $this->be($user)->get('/api/cases/mine');

        $response->assertStatus(200);
        $this->assertAuditUserDetailNotSet($auditRepository);
    }

    private function assertAuditUserDetailSet(MockInterface $auditRepository): void
    {
        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(function (AuditEvent $event): bool {
                $user = $this->getUserFromAuditEvent($event);
                return $user->getDetails()['bcosync'] === true;
            }));
    }

    private function assertAuditUserDetailNotSet(MockInterface $auditRepository): void
    {
        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(function (AuditEvent $event): bool {
                $user = $this->getUserFromAuditEvent($event);
                return !array_key_exists('bcosync', $user->getDetails());
            }));
    }

    private function getUserFromAuditEvent(AuditEvent $event): AuditUser
    {
        return collect($event->getUsers())->sole();
    }
}
