<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-extensive-contact-tracing')]
final class ApiCaseExtensiveContactTracingV1UpTo1ControllerTest extends FeatureTestCase
{
    public function testFragmentCanBeRetrievedWhenEmpty(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 1]);

        $response = $this->be($user)->get($this->generateUri($case));
        $response->assertStatus(200);
    }

    public static function withValidPayloadProvider(): array
    {
        return [
            'no values' => [
                null,
                null,
                null,
            ],
            'only yes' => [
                YesNoUnknown::yes()->value,
                null,
                null,
            ],
            'yes with a single reaon' => [
                YesNoUnknown::yes()->value,
                ['hard-to-reach-group'],
                null,
            ],
            'yes with a single reason and notes' => [
                YesNoUnknown::yes()->value,
                ['hard-to-reach-group'],
                'Some notes..',
            ],
            'yes with multiple reasons' => [
                YesNoUnknown::yes()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                null,
            ],
            'yes with multiple reasons and notes' => [
                YesNoUnknown::yes()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                'Some notes..',
            ],
            'only no' => [
                YesNoUnknown::no()->value,
                null,
                null,
            ],
            'no with a single reaon' => [
                YesNoUnknown::yes()->value,
                ['hard-to-reach-group'],
                null,
            ],
            'no with a single reason and notes' => [
                YesNoUnknown::yes()->value,
                ['hard-to-reach-group'],
                'Some notes..',
            ],
            'no with multiple reasons' => [
                YesNoUnknown::no()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                null,
            ],
            'no with multiple reasons and notes' => [
                YesNoUnknown::no()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                'Some notes..',
            ],
        ];
    }

    #[DataProvider('withValidPayloadProvider')]
    public function testStoreFragment(?string $receivesExtensiveContactTracing, ?array $reasons, ?string $notes): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 1]);

        $uri = $this->generateUri($case);

        $payload = [
            'receivesExtensiveContactTracing' => $receivesExtensiveContactTracing,
            'reasons' => $reasons,
            'notes' => $notes,
        ];

        // store fragment
        $response = $this->be($user)->putJson($uri, $payload);

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($receivesExtensiveContactTracing, $data['data']['receivesExtensiveContactTracing']);
        $this->assertEquals($reasons, $data['data']['reasons']);
        $this->assertEquals($notes, $data['data']['notes']);

        // check if stored values are saved
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($receivesExtensiveContactTracing, $data['data']['receivesExtensiveContactTracing']);
        $this->assertEquals($reasons, $data['data']['reasons']);
        $this->assertEquals($notes, $data['data']['notes']);
    }

    private function generateUri(EloquentCase $case): string
    {
        return sprintf('/api/cases/%s/fragments/extensive-contact-tracing', $case->uuid);
    }
}
