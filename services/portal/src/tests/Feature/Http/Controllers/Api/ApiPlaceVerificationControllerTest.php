<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\Place;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Repositories\AuditRepository;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

use function array_merge;
use function collect;

#[Group('place')]
class ApiPlaceVerificationControllerTest extends FeatureTestCase
{
    public function testCanVerifyPlace(): void
    {
        $place = $this->createPlaceWithUser();

        $this->assertFalse($place->is_verified);

        $response = $this->putJson('api/places/' . $place->uuid . '/verify');
        $response->assertStatus(200);

        $place->refresh();
        $this->assertTrue($place->is_verified);
    }

    public function testVerifyPlaceWritesToAuditLog(): void
    {
        $auditRepository = $this->spy(AuditRepository::class);

        $place = $this->createPlaceWithUser();

        $this->assertFalse($place->is_verified);

        $response = $this->putJson('api/places/' . $place->uuid . '/verify');
        $response->assertStatus(200);

        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($place) {
                /** @var AuditObject $object */
                $object = collect($event->getObjects())->first(static fn (AuditObject $object) => $object->getType() === 'place');
                if ($object === null) {
                    return false;
                }

                return $object->getIdentifier() === $place->uuid;
            }));
    }

    public function testCanVerifyMultiple(): void
    {
        [$place1, $place2] = $this->createPlaceWithUsers();

        $response = $this->putJson('api/places/verifyMulti', ['placeUuids' => [$place2->uuid, $place1->uuid]]);
        $response->assertStatus(200);

        $place1->refresh();
        $place2->refresh();

        $this->assertTrue($place1->is_verified);
        $this->assertTrue($place2->is_verified);
    }

    public function testThrowsExceptionIfVerifyMultipleContainsError(): void
    {
        [$place1, $place2] = $this->createPlaceWithUsers();

        $response = $this->putJson('api/places/verifyMulti', ['placeUuids' => [Uuid::uuid4()->toString(), $place1->uuid]]);
        $response->assertStatus(422);

        $place1->refresh();
        $place2->refresh();

        $this->assertFalse($place1->is_verified);
        $this->assertFalse($place2->is_verified);
    }

    public function testThrowsExceptionIfVerifyMultipleContainsUnauthorized(): void
    {
        $userOrg = $this->createOrganisation();
        $placeOrg = $this->createOrganisation();
        $user = $this->createUserForOrganisation($userOrg, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlace(['is_verified' => false, 'organisation_uuid' => $placeOrg->uuid]);

        $response = $this->putJson('api/places/verifyMulti', ['placeUuids' => [$place->uuid]]);

        $response->assertStatus(422);
    }

    public function testVerifyMultiplePlacesWritesToAuditLog(): void
    {
        $auditRepository = $this->spy(AuditRepository::class);

        [$place1, $place2] = $this->createPlaceWithUsers();

        $response = $this->putJson('api/places/verifyMulti', ['placeUuids' => [$place1->uuid, $place2->uuid]]);
        $response->assertStatus(200);

        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($place1, $place2) {
                $objects = collect($event->getObjects())->filter(static fn (AuditObject $object) => $object->getType() === 'place');

                return $objects->first(static fn(AuditObject $object) => $object->getIdentifier() === $place1->uuid) !== null
                    && $objects->first(static fn(AuditObject $object) => $object->getIdentifier() === $place2->uuid) !== null;
            }));
    }

    public function testCanUnVerifyPlace(): void
    {
        $place = $this->createPlaceWithUser(['is_verified' => true]);

        $this->assertTrue($place->is_verified);

        $response = $this->putJson('api/places/' . $place->uuid . '/unverify');
        $response->assertStatus(200);

        $place->refresh();
        $this->assertFalse($place->is_verified);
    }

    public function testUnVerifyPlaceWritesToAuditLog(): void
    {
        $auditRepository = $this->spy(AuditRepository::class);

        $place = $this->createPlaceWithUser(['is_verified' => true]);

        $this->assertTrue($place->is_verified);

        $response = $this->putJson('api/places/' . $place->uuid . '/unverify');
        $response->assertStatus(200);

        $auditRepository->shouldHaveReceived('registerEvent')
            ->with(Mockery::on(static function (AuditEvent $event) use ($place) {
                /** @var AuditObject $object */
                $object = collect($event->getObjects())->first(static fn (AuditObject $object) => $object->getType() === 'place');
                if ($object === null) {
                    return false;
                }

                return $object->getIdentifier() === $place->uuid;
            }));
    }

    private function createPlaceWithUser(?array $placeAttributes = []): Place
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        return $this->createPlace(array_merge(
            ['is_verified' => false, 'organisation_uuid' => $organisation->uuid],
            $placeAttributes,
        ));
    }

    private function createPlaceWithUsers(): array
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        return [
            $this->createPlace(['is_verified' => false, 'organisation_uuid' => $organisation->uuid]),
            $this->createPlace(['is_verified' => false, 'organisation_uuid' => $organisation->uuid]),
        ];
    }
}
