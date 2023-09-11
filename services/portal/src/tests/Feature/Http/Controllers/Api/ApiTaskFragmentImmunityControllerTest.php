<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Task\Immunity;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentImmunityControllerTest extends FeatureTestCase
{
    #[DataProvider('immuneDataProvider')]
    public function testGetImmunityFragment(?YesNoUnknown $isImmune, ?string $remarks): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'immunity' => Immunity::newInstanceWithVersion(1, static function (Immunity $immunity) use ($remarks, $isImmune): void {
                $immunity->isImmune = $isImmune;
                $immunity->remarks = $remarks;
            }),
            'schemaVersion' => 1,
        ]);

        $expectedResponseData = [
            'isImmune' => $isImmune,
            'remarks' => $remarks,
            'schemaVersion' => 1,
        ];

        $response = $this->be($user)->getJson(sprintf('/api/tasks/%s/fragments/immunity', $task->uuid));
        $response->assertStatus(200);
        $dataGet = $response->json('data');

        $this->assertEquals($expectedResponseData, $dataGet);
    }

    public function testPutImmunityFragmentWithEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'schemaVersion' => 1,
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s/fragments/immunity', $task->uuid), []);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertFalse(isset($data['validationResult']));
    }

    #[DataProvider('immuneDataProvider')]
    public function testPutImmunityFragmentWithFullPayload(?YesNoUnknown $isImmune, ?string $remarks): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'schemaVersion' => 1,
        ]);

        $payload = [
            'isImmune' => $isImmune,
            'remarks' => $remarks,
        ];
        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s/fragments/immunity', $task->uuid), $payload);
        $response->assertStatus(200);

        $this->assertEquals([
            'data' => [
                'isImmune' => $isImmune,
                'remarks' => $remarks,
                'schemaVersion' => 1,
            ],
        ], $response->json());
    }

    public static function immuneDataProvider(): array
    {
        return [
            [null, null],
            [null, 'Foo'],
            [YesNoUnknown::yes(), null],
            [YesNoUnknown::yes(), 'Foo'],
        ];
    }
}
