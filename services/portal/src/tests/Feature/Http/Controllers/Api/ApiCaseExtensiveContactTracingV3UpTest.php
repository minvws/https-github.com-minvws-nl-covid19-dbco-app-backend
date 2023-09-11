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
final class ApiCaseExtensiveContactTracingV3UpTest extends FeatureTestCase
{
    public function testFragmentCanBeRetrievedWhenEmpty(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 5]);

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
            ],
            'only no' => [
                BCOType::standard()->value,
                null,
                null,
            ],
        ];
    }

    #[DataProvider('withValidPayloadProvider')]
    public function testStoreFragment(?string $receivesExtensiveContactTracing): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 5]);

        $uri = $this->generateUri($case);

        $payload = [
            'receivesExtensiveContactTracing' => $receivesExtensiveContactTracing,
        ];

        // store fragment
        $response = $this->be($user)->putJson($uri, $payload);

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($receivesExtensiveContactTracing, $data['data']['receivesExtensiveContactTracing']);

        // check if stored values are saved
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($receivesExtensiveContactTracing, $data['data']['receivesExtensiveContactTracing']);
    }

    private function generateUri(EloquentCase $case): string
    {
        return sprintf('/api/cases/%s/fragments/extensive-contact-tracing', $case->uuid);
    }
}
