<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Immunity;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function json_encode;
use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-immunity')]
class ApiCaseImmunityControllerTest extends FeatureTestCase
{
    #[DataProvider('immuneDataProvider')]
    public function testGet(?YesNoUnknown $isImmune, ?string $remarks): void
    {
        $immunity = Immunity::getSchema()->getVersion(1)->newInstance();
        $immunity->isImmune = $isImmune;
        $immunity->remarks = $remarks;

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'immunity' => $immunity,
        ]);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/fragments/immunity', $case->uuid));
        $response->assertStatus(200);

        $expectedContent = [
            'data' => [
                'schemaVersion' => 1,
                'isImmune' => null,
                'remarks' => null,
            ],
        ];
        $this->assertJson(json_encode($expectedContent), $response->getContent());
    }

    public function testGetNonExisting(): void
    {
        $user = $this->createUser();
        $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/immunity');
        $response->assertStatus(404);
    }

    public function testPostEmptyPayload(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/immunity', $case->uuid), []);
        $response->assertStatus(200);
    }

    #[DataProvider('immuneDataProvider')]
    public function testPost(?YesNoUnknown $isImmune, ?string $remarks): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['schema_version' => 1]);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/fragments/immunity', $case->uuid), [
            'isImmune' => $isImmune ? $isImmune->value : null,
            'remarks' => $remarks,
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($isImmune ? $isImmune->value : null, $data['data']['isImmune']);
        $this->assertEquals($remarks, $data['data']['remarks']);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/immunity');
        $data = $response->json();
        $this->assertEquals($isImmune ? $isImmune->value : null, $data['data']['isImmune']);
        $this->assertEquals($remarks, $data['data']['remarks']);
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
