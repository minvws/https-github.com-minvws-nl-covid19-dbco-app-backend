<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\BCOType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-extensive-contact-tracing')]
final class ApiCaseExtensiveContactTracingV2UpTo2ControllerTest extends FeatureTestCase
{
    public function testFragmentCanBeRetrievedWhenEmpty(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 4]);

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
                BCOType::extensive()->value,
                null,
                null,
            ],
            'yes with a single reaon' => [
                BCOType::extensive()->value,
                ['hard-to-reach-group'],
                null,
            ],
            'yes with a single reason and notes' => [
                BCOType::extensive()->value,
                ['hard-to-reach-group'],
                'Some notes..',
            ],
            'yes with multiple reasons' => [
                BCOType::extensive()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                null,
            ],
            'yes with multiple reasons and notes' => [
                BCOType::extensive()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                'Some notes..',
            ],
            'only no' => [
                BCOType::standard()->value,
                null,
                null,
            ],
            'no with a single reaon' => [
                BCOType::extensive()->value,
                ['hard-to-reach-group'],
                null,
            ],
            'no with a single reason and notes' => [
                BCOType::extensive()->value,
                ['hard-to-reach-group'],
                'Some notes..',
            ],
            'no with multiple reasons' => [
                BCOType::standard()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                null,
            ],
            'no with multiple reasons and notes' => [
                BCOType::standard()->value,
                ['hard-to-reach-group', 'risk-voc-voi'],
                'Some notes..',
            ],
        ];
    }

    #[DataProvider('withValidPayloadProvider')]
    public function testStoreFragment(?string $receivesExtensiveContactTracing, ?array $reasons, ?string $notes): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 4]);

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
