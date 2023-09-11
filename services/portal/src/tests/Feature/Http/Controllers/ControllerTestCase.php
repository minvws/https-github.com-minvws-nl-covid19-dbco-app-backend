<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Repositories\AuditRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

class ControllerTestCase extends FeatureTestCase
{
    private MockInterface|AuditRepository $auditRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditRepository = $this->spy(AuditRepository::class);
    }

    protected function assertAuditEventForCase(EloquentCase $case): void
    {
        $this->auditRepository
            ->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($case): bool {
                $auditObject = $event->getObjects()[0];
                return $auditObject->getType() === 'case'
                    && $auditObject->getIdentifier() === $case->uuid;
            }));
    }

    protected function assertAuditObjectForOrganisation(EloquentOrganisation $organisation): void
    {
        $this->auditRepository
            ->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($organisation): bool {
                $auditObject = $event->getObjects()[0];
                return $auditObject->getDetails()['organisationId'] === $organisation->external_id
                    && $auditObject->getDetails()['organisationName'] === $organisation->name;
            }));
    }

    protected function assertAuditObjectForUser(EloquentUser $user): void
    {
        $this->auditRepository
            ->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($user): bool {
                $auditObject = $event->getObjects()[0];
                return $auditObject->getType() === 'user'
                    && $auditObject->getIdentifier() === $user->uuid;
            }));
    }
}
