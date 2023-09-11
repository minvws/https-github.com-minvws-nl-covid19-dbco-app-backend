<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Jobs\UpdatePlaceCounters;
use App\Models\CovidCase\Test;
use App\Models\Eloquent\Context;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('context')]
class ApiContextControllerTest extends FeatureTestCase
{
    public function testUpdateContextMinimalData(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->putJson(sprintf('api/contexts/%s', $context->uuid), [
            'context' => ['foo'],
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $expectedResponseData = [
            'context' => [
                'uuid' => $context->uuid,
                'label' => null,
                'explanation' => null,
                'detailedExplanation' => null,
                'remarks' => null,
                'placeUuid' => null,
                'moments' => [],
                'relationship' => null,
                'otherRelationship' => null,
                'isSource' => false,
                'place' => null,
            ],
        ];

        $response->assertJson($expectedResponseData);
    }

    public function testUpdateContextFullData(): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        /** @var Test $test */
        $test = Test::getSchema()->getCurrentVersion()->newInstance();
        $test->dateOfSymptomOnset = CarbonImmutable::yesterday();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'test' => $test,
        ]);
        $context = $this->createContextForCase($case);
        $place = $this->createPlace(['is_verified' => false]);

        $response = $this->be($user)->putJson(sprintf('api/contexts/%s', $context->uuid), [
            'context' => [
                'uuid' => $context->uuid,
                'label' => 'foo',
                'explanation' => 'bar',
                'detailedExplanation' => 'foobar',
                'remarks' => 'some remarks',
                'placeUuid' => $place->uuid,
                'moments' => [
                    CarbonImmutable::now()->subDays(3)->format('Y-m-d'),
                ],
                'relationship' => 'teacher',
                'otherRelationship' => 'father',
                'isSource' => true,
            ],
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $expectedResponseData = [
            'context' => [
                'uuid' => $context->uuid,
                'label' => 'foo',
                'explanation' => 'bar',
                'detailedExplanation' => 'foobar',
                'remarks' => 'some remarks',
                'placeUuid' => $place->uuid,
                'moments' => [
                    '2019-12-29',
                ],
                'relationship' => 'teacher',
                'otherRelationship' => 'father',
                'isSource' => true,
                'place' => [
                    'uuid' => $place->uuid,
                    'label' => $place->label,
                    'category' => $place->category,
                    'address' => [
                        'postalCode' => $place->postalcode,
                        'street' => $place->street,
                        'houseNumber' => $place->housenumber,
                        'houseNumberSuffix' => $place->housenumber_suffix,
                        'town' => $place->town,
                        'country' => $place->country,
                    ],
                    'editable' => true,
                    'isVerified' => false,
                ],
            ],
        ];
        $response->assertJson($expectedResponseData);
    }

    public function testLinkPlaceToContext(): void
    {
        $oldPlace = $this->createPlace();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case, [
            'place_uuid' => $oldPlace->uuid,
        ]);
        $place = $this->createPlace();

        $response = $this->be($user)->post(sprintf('api/contexts/%s/place/%s', $context->uuid, $place->uuid));

        $this->assertEquals(201, $response->getStatusCode());

        $this->assertDatabaseHas(Context::class, ['uuid' => $context->uuid, 'place_uuid' => $place->uuid]);
        $this->assertDatabaseMissing(Context::class, ['uuid' => $context->uuid, 'place_uuid' => $oldPlace->uuid]);
    }

    public function testUnlinkPlaceFromContext(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $place = $this->createPlace();
        $context = $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);

        $response = $this->be($user)->delete(sprintf('api/contexts/%s/place/%s', $context->uuid, $place->uuid));
        $this->assertEquals(204, $response->getStatusCode());

        $this->assertDatabaseHas(Context::class, ['uuid' => $context->uuid, 'place_uuid' => null]);
    }

    public function testUnlinkInvalidPlaceFromContext(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $place = $this->createPlace();
        $invalidPlace = $this->createPlace();
        $context = $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);

        $response = $this->be($user)->delete(sprintf('api/contexts/%s/place/%s', $context->uuid, $invalidPlace->uuid));
        $this->assertEquals(400, $response->getStatusCode());

        $this->assertDatabaseHas(Context::class, ['uuid' => $context->uuid, 'place_uuid' => $place->uuid]);
    }

    public function testCreatedContextWillDispatchUpdatePlaceCountersJob(): void
    {
        Bus::fake(UpdatePlaceCounters::class);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $place = $this->createPlace();

        $this->be($user)->postJson(sprintf('api/cases/%s/contexts', $case->uuid), [
            'context' => [
                'placeUuid' => $place->uuid,
            ],
        ]);

        Bus::assertDispatched(UpdatePlaceCounters::class);
    }

    public function testUpdatingContextWillDispatchUpdatePlaceCountersJob(): void
    {
        Bus::fake(UpdatePlaceCounters::class);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $place = $this->createPlace();

        /** @var Context $context Make sure the context is created quietly as it would trigger the "created" observer */
        $context = Context::factory()->make([
            'covidcase_uuid' => $case->uuid,
            'place_uuid' => $place->uuid,
        ]);
        $context->saveQuietly();

        // Place uuid needs to be send through else it will unlink is automatically
        $this->be($user)->putJson(sprintf('api/contexts/%s', $context->uuid), [
            'context' => [
                'label' => 'foo',
                'placeUuid' => $place->uuid,
            ],
        ]);

        Bus::assertDispatched(UpdatePlaceCounters::class);
    }
}
