<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Place;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function in_array;
use function sprintf;

#[Group('case')]
#[Group('case-context')]
final class ApiCaseContextControllerTest extends FeatureTestCase
{
    public function testPostContextMinimum(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->be($user);

        // check minimum fields required for storage
        $response = $this->postJson('/api/cases/' . $case->uuid . '/contexts', [
            'context' => [
                'label' => 'Voetbalvereniging',
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['context']['isSource']);
    }

    public function testPostContextFull(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->be($user);

        $response = $this->postJson('/api/cases/' . $case->uuid . '/contexts', [
            'context' => [
                'label' => 'Voetbalvereniging',
                'explanation' => 'Feestje',
                'detailedExplanation' => 'Feestje van de vereniging, was heel gezellig',
                'remarks' => 'Waren er meer mensen aanwezig? Ja',
                'isSource' => 1,
            ],
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['context']['isSource']);
    }

    #[DataProvider('postContextValidationErrorDataProvider')]
    public function testPostContextValidation(array $postData): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->be($user);

        $response = $this->postJson(sprintf('/api/cases/%s/contexts', $case->uuid), $postData);
        $response->assertStatus(422);
    }

    public static function postContextValidationErrorDataProvider(): array
    {
        return [
            'no data' => [[]],
            'empty context' => [['context' => []]],
        ];
    }

    public function testCreateAndUpdateContextFull(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => CarbonImmutable::today(),
            'date_of_test' => CarbonImmutable::today(),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $this->be($user);

        $createResponse = $this->postJson('/api/cases/' . $case->uuid . '/contexts', [
            'context' => [
                'label' => 'Voetbalvereniging',
            ],
        ]);
        $createResponse->assertStatus(200);
        $data = $createResponse->json();
        $this->assertFalse($data['context']['isSource']);

        $momentDate = CarbonImmutable::now()->subDays(5)->format("Y-m-d");
        $updateResponse = $this->putJson('/api/contexts/' . $data['context']['uuid'], [
            'context' => [
                'label' => 'Voetbalvereniging',
                'moments' => [$momentDate],
                'explanation' => 'Feestje',
                'detailedExplanation' => 'Feestje van de vereniging, was heel gezellig',
                'remarks' => 'Waren er meer mensen aanwezig? Ja',
                'isSource' => true,
                'relationship' => ContextRelationship::other(),
                'otherRelationship' => 'test',
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertTrue($updateData['context']['isSource']);
        $this->assertTrue(in_array($momentDate, $updateData['context']['moments'], true));
        $this->assertEquals($updateData['context']['otherRelationship'], 'test');
    }

    public function testContextReturnsEmbedPlace(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->be($user);

        $placePayload = [
            'label' => 'hello',
            'address' => [
                "postalCode" => "1234AB",
                "street" => "street",
                "houseNumber" => "5",
                "houseNumberSuffix" => null,
                "town" => "place",
                "country" => "NL",
            ],
            "category" => "buitenland",
            "locationId" => null,
        ];
        $createPlaceResponse = $this->postJson('/api/places', $placePayload);
        $createPlaceResponse->assertStatus(201);
        $placeUuid = $createPlaceResponse->json()['uuid'];

        $createResponse = $this->postJson('/api/cases/' . $case->uuid . '/contexts', [
            'context' => [
                'label' => 'Voetbalvereniging',
                'placeUuid' => $placeUuid,
            ],
        ]);
        $createResponse->assertStatus(200);
        $context = $createResponse->json()['context'];

        $this->assertEquals($placeUuid, $context['place']['uuid']);
        $this->assertEquals($placePayload['label'], $context['place']['label']);
    }

    #[Group('case-context-section')]
    public function testContextSections(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $case = $this->createCaseForUser($user);
        /** @var Place $place */
        $place = Place::factory()->create([
            'label' => 'My Special Place',
        ]);
        $context = $this->createContextForCase($case, [
            'label' => 'Unique Context',
            'place_uuid' => $place->uuid,
            'relationship' => ContextRelationship::teacher(),
        ]);
        Moment::factory()->create([
            'context_uuid' => $context->uuid,
        ]);

        $listResponse = $this->getJson('/api/contexts/' . $context->uuid . '/sections');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonCount(0, 'sections');

        $section = $this->createSectionForPlace($place);

        $createResponse = $this->post('/api/contexts/' . $context->uuid . '/sections/' . $section->uuid);
        $createResponse->assertStatus(201);

        $listResponse = $this->getJson('/api/contexts/' . $context->uuid . '/sections');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonCount(1, 'sections');
        $sections = $listResponse->json('sections');
        $this->assertArrayHasKey('label', $sections[0]);
        $this->assertEquals($section->label, $sections[0]['label']);

        $deleteResponse = $this->delete('/api/contexts/' . $context->uuid . '/sections/' . $section->uuid);
        $deleteResponse->assertStatus(204);

        $listResponse = $this->getJson('/api/contexts/' . $context->uuid . '/sections');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonCount(0, 'sections');
    }

    public function testContextUnlinkPlaceShouldUnlinkPlace(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $case = $this->createCaseForUser($user);

        /** @var Place $place */
        $place = Place::factory()->create([
            'label' => 'My Special Place',
        ]);

        $context = $this->createContextForCase($case, [
            'label' => 'Unique Context',
            'place_uuid' => $place->uuid,
        ]);

        $updateResponse = $this->putJson('/api/contexts/' . $context->uuid, [
            'context' => [
                'label' => 'Unique Context',
                'placeUuid' => null,
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertNull($updateData['context']['placeUuid']);
    }

    public function testContextReplacePlaceShouldUnlinkPreviousPlace(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $case = $this->createCaseForUser($user);

        /** @var Place $linkedPlace */
        $linkedPlace = Place::factory()->create([
            'label' => 'Current linked Place',
            'is_verified' => false,
        ]);

        /** @var Place $newPlace */
        $newPlace = Place::factory()->create([
            'label' => 'new Place',
            'is_verified' => false,
        ]);

        $context = $this->createContextForCase($case, [
            'label' => 'Unique Context',
            'place_uuid' => $linkedPlace->uuid,
        ]);

        $updateResponse = $this->putJson('/api/contexts/' . $context->uuid, [
            'context' => [
                'label' => 'Unique Context',
                'placeUuid' => $newPlace->uuid,
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertEquals($newPlace->uuid, $updateData['context']['placeUuid']);
        $this->assertDatabaseMissing('place', ['uuid' => $linkedPlace->uuid]);
    }

    /**
     * Tests that a place unlinked from a context also removes completely the dangled sections.
     * A dangled section is a section that is not linked to any other context than the context where the place will be
     * removed from.
     *
     * Following use case configures 2 contexts A and B linked both to the same place but one with 2 sections and the
     * other one with only 1 section.
     *
     * Context A - Section A
     * Context B - Section A and Section B
     *
     * This test will remove the place from Context B. Since Section B will not be linked to any other Context,
     * Section B should be completely removed!
     */
    public function testContextUnlinkPlaceShouldRemoveDangledSections(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $caseA = $this->createCaseForUser($user);

        /** @var Place $place */
        $place = Place::factory()->create([
            'label' => 'My Special Place',
        ]);

        $sectionA = $this->createSectionForPlace($place, ['label' => 'My Favourite Spot A']);
        $sectionB = $this->createSectionForPlace($place, ['label' => 'My Favourite Spot B']);
        $sectionC = $this->createSectionForPlace($place, ['label' => 'The Blind Spot In My Dark Place']);

        $contextA = $this->createContextForCase($caseA, [
            'label' => 'Context A',
            'place_uuid' => $place->uuid,
        ]);
        $contextA->sections()->saveMany([$sectionA, $sectionC]);

        $contextB = $this->createContextForCase($caseA, [
            'label' => 'Context B',
            'place_uuid' => $place->uuid,
        ]);
        $contextB->sections()->saveMany([$sectionA, $sectionB]);

        $this->assertDatabaseHas(
            'context_section',
            ['context_uuid' => $contextA->uuid, 'section_uuid' => $sectionA->uuid],
        );
        $this->assertDatabaseHas(
            'context_section',
            ['context_uuid' => $contextA->uuid, 'section_uuid' => $sectionC->uuid],
        );
        $this->assertDatabaseHas(
            'context_section',
            ['context_uuid' => $contextB->uuid, 'section_uuid' => $sectionA->uuid],
        );
        $this->assertDatabaseHas(
            'context_section',
            ['context_uuid' => $contextB->uuid, 'section_uuid' => $sectionB->uuid],
        );

        $updateResponse = $this->putJson('/api/contexts/' . $contextB->uuid, [
            'context' => [
                'label' => 'Context B',
                'placeUuid' => null,
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertNull($updateData['context']['placeUuid']);

        $this->assertDatabaseMissing(
            'context_section',
            ['context_uuid' => $contextB->uuid, 'section_uuid' => $sectionA->uuid],
        );
        $this->assertDatabaseMissing(
            'context_section',
            ['context_uuid' => $contextB->uuid, 'section_uuid' => $sectionB->uuid],
        );
        $this->assertDatabaseHas(
            'context_section',
            ['context_uuid' => $contextA->uuid, 'section_uuid' => $sectionC->uuid],
        );

        // Temporary hotfix to prevent data-loss in production, @see https://egeniq.atlassian.net/browse/DBCO-4038
        $this->assertDatabaseHas(
            'section',
            ['uuid' => $sectionB->uuid],
        );
        $this->assertDatabaseHas(
            'section',
            ['uuid' => $sectionA->uuid],
        );
    }

    /**
     * Tests that a place unlinked from a context also removes completely the place IF that place is not linked
     * to any other context.
     */
    public function testContextUnlinkPlaceShouldRemovePlaceIfNotLinkedToAnyOtherContext(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $caseA = $this->createCaseForUser($user);
        $caseB = $this->createCaseForUser($user);

        /** @var Place $placeA */
        $placeA = Place::factory()->create([
            'label' => 'Place linked to multiple contexts',
            'is_verified' => false,
        ]);

        /** @var Place $placeB */
        $placeB = Place::factory()->create([
            'label' => 'Place linked to only 1 context',
            'is_verified' => false,
        ]);


        $contextA1 = $this->createContextForCase($caseA, [
            'label' => 'Context A1 on Case A',
            'place_uuid' => $placeA->uuid,
        ]);

        $contextA2 = $this->createContextForCase($caseA, [
            'label' => 'Context A2 on Case A',
            'place_uuid' => $placeB->uuid,
        ]);

        $this->createContextForCase($caseB, [
            'label' => 'Context B on Case B',
            'place_uuid' => $placeA->uuid,
        ]);

        // We remove placeA from Context A1, but since PlaceA is also linked to Context B, it should still exist
        $updateResponse = $this->putJson('/api/contexts/' . $contextA1->uuid, [
            'context' => [
                'label' => 'Context A1',
                'placeUuid' => null,
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertNull($updateData['context']['placeUuid']);
        $this->assertDatabaseHas('place', ['uuid' => $placeA->uuid]);

        // We remove placeB from Context A2, and since PlaceB is not linked to any other context it will be removed
        $updateResponse = $this->putJson('/api/contexts/' . $contextA2->uuid, [
            'context' => [
                'label' => 'Context A2',
                'placeUuid' => null,
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertNull($updateData['context']['placeUuid']);
        $this->assertDatabaseMissing('place', ['uuid' => $placeB->uuid]);
    }

    public function testContextUnlinkPlaceShouldNotRemovePlaceIfVerified(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $caseA = $this->createCaseForUser($user);
        $caseB = $this->createCaseForUser($user);

        /** @var Place $placeA */
        $placeA = Place::factory()->create([
            'label' => 'Place linked to multiple contexts',
            'is_verified' => true,
        ]);

        /** @var Place $placeB */
        $placeB = Place::factory()->create([
            'label' => 'Place linked to only 1 context',
            'is_verified' => true,
        ]);

        $contextA1 = $this->createContextForCase($caseA, [
            'label' => 'Context A1 on Case A',
            'place_uuid' => $placeA->uuid,
        ]);

        $contextA2 = $this->createContextForCase($caseA, [
            'label' => 'Context A2 on Case A',
            'place_uuid' => $placeB->uuid,
        ]);

        $this->createContextForCase($caseB, [
            'label' => 'Context B on Case B',
            'place_uuid' => $placeA->uuid,
        ]);

        // We remove placeA from Context A1, but since PlaceA is also linked to Context B, it should still exist
        $updateResponse = $this->putJson('/api/contexts/' . $contextA1->uuid, [
            'context' => [
                'label' => 'Context A1',
                'placeUuid' => null,
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertNull($updateData['context']['placeUuid']);
        $this->assertDatabaseHas('place', ['uuid' => $placeA->uuid]);

        // We remove placeB from Context A2, and since PlaceB is not linked to any other context it will be removed
        $updateResponse = $this->putJson('/api/contexts/' . $contextA2->uuid, [
            'context' => [
                'label' => 'Context A2',
                'placeUuid' => null,
            ],
        ]);
        $updateResponse->assertStatus(200);
        $updateData = $updateResponse->json();
        $this->assertNull($updateData['context']['placeUuid']);
        $this->assertDatabasehas('place', ['uuid' => $placeB->uuid]);
    }

    public function testContextIncludesForEmbedPlaceEditableInfo(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $caseA = $this->createCaseForUser($user);
        $caseB = $this->createCaseForUser($user);
        $caseC = $this->createCaseForUser($user);

        /** @var Place $placeA */
        $placeA = Place::factory()->create([
            'label' => 'Place linked to multiple contexts',
        ]);

        /** @var Place $placeB */
        $placeB = Place::factory()->create([
            'label' => 'Place linked to only 1 context',
        ]);

        $this->createContextForCase($caseA, [
            'label' => 'Context A on Case A with place used in other case/context',
            'place_uuid' => $placeA->uuid,
        ]);

        $this->createContextForCase($caseB, [
            'label' => 'Context B on Case B with place only used in this case/context',
            'place_uuid' => $placeB->uuid,
        ]);

        $this->createContextForCase($caseC, [
            'label' => 'Context C on Case C with place used in other case/context',
            'place_uuid' => $placeA->uuid,
        ]);

        $response = $this->getJson('/api/cases/' . $caseA->uuid . '/contexts');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['contexts'][0]['place']['editable']);

        $response = $this->getJson('/api/cases/' . $caseB->uuid . '/contexts');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['contexts'][0]['place']['editable']);

        $response = $this->getJson('/api/cases/' . $caseC->uuid . '/contexts');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['contexts'][0]['place']['editable']);
    }

    public function testPlaceAndContextSectionsReturnValidIndexCount(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $case1 = $this->createCaseForUser($user);
        $case2 = $this->createCaseForUser($user);

        /** @var Place $place */
        $place = Place::factory()->create([
            'label' => 'Place linked to 2 cases/indexes',
        ]);
        $sectionMulti = $this->createSectionForPlace($place);
        $sectionSingle = $this->createSectionForPlace($place);

        $this->createContextForCase(
            $case1,
            [
                'label' => 'Unique Context',
                'place_uuid' => $place->uuid,
            ],
            [$sectionMulti],
        );

        $contextCandidate = $this->createContextForCase(
            $case2,
            [
                'label' => 'Unique Context',
                'place_uuid' => $place->uuid,
            ],
            [$sectionMulti, $sectionSingle],
        );

        foreach (['/api/contexts/' . $contextCandidate->uuid . '/sections', '/api/places/' . $place->uuid . '/sections'] as $url) {
            $listResponse = $this->getJson($url);
            $listResponse->assertStatus(200);
            $listResponse->assertJsonCount(2, 'sections');
            $sections = $listResponse->json('sections');

            $expectedCounts = [$sectionMulti->uuid => 2, $sectionSingle->uuid => 1];

            foreach ($sections as $section) {
                $this->assertArrayHasKey($section['uuid'], $expectedCounts);
                $this->assertEquals($section['indexCount'], $expectedCounts[$section['uuid']]);
            }
        }
    }

    public function testContextContainsEmptyPlaceDataDoesNotFail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        /** @var Place $place */
        $place = Place::factory()->create([
            'postalcode' => null,
            'street' => null,
            'housenumber' => null,
            'housenumber_suffix' => null,
            'town' => null,
            'country' => 'NL',
        ]);
        $this->createContextForCase($case, [
            'label' => 'Unique Context',
            'place_uuid' => $place->uuid,
            'relationship' => ContextRelationship::teacher(),
        ]);

        $createResponse = $this->be($user)->get(sprintf('/api/cases/%s/contexts', $case->uuid));
        $createResponse->assertStatus(200);
    }
}
