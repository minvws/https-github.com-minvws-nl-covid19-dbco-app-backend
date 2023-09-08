<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Client\Guzzle\MittensClient;
use App\Models\CovidCase\Index;
use App\Models\OrganisationType;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\BsnServiceException;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Bsn\Mittens\MittensBsnRepository;
use App\Services\Bsn\BsnService;
use App\Services\MetricService;
use Carbon\CarbonImmutable;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Tests\Feature\FeatureTestCase;

use function json_encode;
use function sprintf;
use function substr;

#[Group('bsn')]
#[Group('guzzle')]
#[Group('planner-case')]
class ApiPlannerCaseControllerBsnTest extends FeatureTestCase
{
    public function testCreateNewCaseBsnCensoredNotInResponseWhenGuidNotGiven(): void
    {
        $user = $this->createUser([], 'planner', []);
        $this->be($user);

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzone_number' => '1234567',
                'notes' => 'a note',
            ],
        ];

        $response = $this->postJson('/api/cases', $caseData);
        $response->assertStatus(201);
        $this->assertSame(null, $response->json()['data']['index']['bsnCensored']);
    }

    public function testCreateNewCaseBsnCensoredInResponseWhenGuidGiven(): void
    {
        $user = $this->createUser([], 'planner', []);
        $this->be($user);

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzone_number' => '1234567',
                'notes' => 'a note',
            ],
            'pseudoBsnGuid' => '1eaf0d45-1124-4799-931d-58f628635079',
        ];

        $response = $this->postJson('/api/cases', $caseData);
        $response->assertStatus(201);
        $this->assertSame('******' . substr('999998286', -3), $response->json()['data']['index']['bsnCensored']);
    }

    public function testCreateCaseWithInvalidPseudoBsnGuidShouldFail(): void
    {
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);

        $organisation = $planner->organisations->first();

        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')->andThrow(BsnException::class);
        });

        // create case with duplicate reference
        $phone = '06-12345678';
        $dateOfTest = CarbonImmutable::yesterday()->format('Y-m-d');
        $response = $this->be($planner)->postJson('/api/cases', [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => $phone,
            ],
            'test' => [
                'dateOfTest' => $dateOfTest,
            ],
            'general' => [
                'organisation' => [
                    'uuid' => $organisation->uuid,
                ],
                'reference' => '1234567',
                'notes' => '...',
            ],
            'pseudoBsnGuid' => '6ee175b5-d456-4718-bea6-cba38fd23806',
        ]);

        $this->assertStatus($response, 422);
        $data = $response->json();
        $this->assertArrayHasKey('pseudoBsnGuid', $data['validationResult']['fatal']['errors']);
        $this->assertEquals(
            ['No BSN found with given value'],
            $data['validationResult']['fatal']['errors']['pseudoBsnGuid'],
        );
    }

    public function testUpdateCaseWithInvalidPseudoBsnGuidShouldFail(): void
    {
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);

        $case = $this->createCaseForUser($planner, [
            'case_id' => '1234567',
            'pseudoBsnGuid' => '6ee175b5-d456-4718-bea6-cba38fd23806',
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'bco_status' => BCOStatus::open(),
        ]);

        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')->andThrow(BsnException::class);
        });

        // create case with duplicate reference
        $response = $this->be($planner)->putJson('/api/cases/planner/' . $case->uuid, [
            'pseudoBsnGuid' => '3b1a7aa5-f9a2-4f07-ad84-e24c9245ddc0',
        ]);

        $this->assertStatus($response, 422);
        $data = $response->json();
        $this->assertArrayHasKey('pseudoBsnGuid', $data['validationResult']['fatal']['errors']);
        $this->assertEquals(
            ['No BSN found with given value'],
            $data['validationResult']['fatal']['errors']['pseudoBsnGuid'],
        );
    }

    public function testUpdateCaseWithSamePseudoBsnGuidShouldNotValidateBsn(): void
    {
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);

        $index = Index::newInstanceWithVersion(1);
        $index->bsnCensored = 'foo';
        $index->bsnLetters = 'bar';

        $case = $this->createCaseForUser($planner, [
            'case_id' => '1234567',
            'pseudoBsnGuid' => '6ee175b5-d456-4718-bea6-cba38fd23806',
            'index' => $index,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'bco_status' => BCOStatus::open(),
        ]);

        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')->never();
        });

        // create case with duplicate reference
        $response = $this->be($planner)->putJson(sprintf('/api/cases/planner/%s', $case->uuid), [
            'pseudoBsnGuid' => '6ee175b5-d456-4718-bea6-cba38fd23806',
        ]);
        $this->assertStatus($response, 200);

        // validate bsn not reset
        $response = $this->be($planner)->getJson('/api/cases/planner/' . $case->uuid);
        $this->assertStatus($response, 200);

        $this->assertEquals('foo', $response->json()['data']['index']['bsnCensored']);
        $this->assertEquals('bar', $response->json()['data']['index']['bsnLetters']);
    }

    public function testCreateNewCaseBsnWhenGuidGivenAndSecondBsnRequestFails(): void
    {
        $user = $this->createUser([], 'planner', []);
        $this->be($user);

        $this->mock(BsnService::class)
            ->expects('getByPseudoBsnGuid')
            ->twice()
            ->andReturnUsing(
                static function () {
                    static $counter = 0;

                    if ($counter > 0) {
                        throw new BsnServiceException('bsn service unavailable');
                    }

                    $counter++;

                    return new PseudoBsn('1eaf0d45-1124-4799-931d-58f628635079', '******837', 'AA');
                },
            );

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
                'notes' => 'a note',
            ],
            'pseudoBsnGuid' => '1eaf0d45-1124-4799-931d-58f628635079',
        ];

        $response = $this->postJson('/api/cases', $caseData);
        $response->assertStatus(201);
        $this->assertSame('1234567', $response->json()['data']['general']['hpzoneNumber']);
        $this->assertSame(null, $response->json()['data']['index']['bsnCensored']);
    }

    public function testCreateNewCaseShouldFailWhenMittensApiGivesServerException(): void
    {
        $user = $this->createUser([], 'planner', []);
        $this->be($user);

        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')
                ->andThrow(new BsnServiceException('service unavailable', 422));
        });
        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'reference' => '1234567',
                'notes' => 'a note',
            ],
            'pseudoBsnGuid' => '1eaf0d45-1124-4799-931d-58f628635079',
        ];

        $response = $this->postJson('/api/cases', $caseData);
        $response->assertStatus(422);
        $data = $response->json();
        $this->assertArrayHasKey('pseudoBsnGuid', $data['validationResult']['fatal']['errors']);
        $this->assertEquals(
            ['BSN service not available'],
            $data['validationResult']['fatal']['errors']['pseudoBsnGuid'],
        );
    }

    public function testRetryMittensRequestAfterServerException(): void
    {
        $container = [];
        $history = Middleware::history($container);

        $responseGuid = $this->faker->uuid();
        $responseCensoredBsn = $this->faker->regexify('******[0-9]{3}');
        $responseLetters = $this->faker->word();

        $responseData = [
            'data' => [
                (object) [
                    'guid' => $responseGuid,
                    'censored_bsn' => $responseCensoredBsn,
                    'letters' => $responseLetters,
                ],
            ],
        ];

        $mock = new MockHandler([
            new ServerException(
                'Error Communicating with Server',
                new Request('POST', '/service/via_uuid'),
                new Response(500, ['Content-Type' => 'application/json'], json_encode([])),
            ),
            new Response(202, ['Content-Type' => 'application/json'], json_encode($responseData)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mittensBsnRepository = new MittensBsnRepository(
            new MittensClient(
                new Client(['handler' => $handlerStack]),
                $this->app->get(MetricService::class),
                $this->createMock(LoggerInterface::class),
            ),
            ['01003' => 'digid_01003'],
            ['01003' => 'pii_01003'],
        );
        $result = $mittensBsnRepository->getByPseudoBsnGuid('pseudoGuid', '01003');

        $this->assertCount(2, $container);
        $this->assertEquals(new PseudoBsn($responseGuid, $responseCensoredBsn, $responseLetters), $result[0]);
    }
}
