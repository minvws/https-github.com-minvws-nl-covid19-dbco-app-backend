<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Jobs\ImportTestResultReport;
use App\Services\TestResultReportService;
use Faker\Factory;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

use function array_key_exists;
use function collect;
use function config;
use function json_encode;
use function sprintf;

#[Group('test-result')]
final class ReportTestResultControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    /**
     * @dataProvider reportTestResultValidationValidDataProvider
     */
    public function testProcessTestResultReport(array $payload): void
    {
        $response = $this->doRequest($payload);
        $response->assertStatus(Response::HTTP_ACCEPTED);

        Queue::assertPushed(ImportTestResultReport::class);
    }

    public static function reportTestResultValidationValidDataProvider(): array
    {
        $validPayload = self::getRequestPayloadForTestResult();
        $faker = Factory::create('nl_NL');

        $person = $validPayload['person'];

        return [
            'default' => [$validPayload],
            'person.bsn null' => [collect($validPayload)->put('person', collect($person)->put('bsn', null))->toArray()],
            'person.bsn numeric' => [collect($validPayload)->put('person', collect($person)->put('bsn', (string) $faker->randomNumber()))->toArray()],
        ];
    }

    /**
     * @dataProvider reportTestResultValidationInvalidDataProvider
     */
    public function testValidateRequiredFields(array $payload): void
    {
        $result = $this->doRequest($payload);

        $result->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $result->assertJson(['message' => 'The given data was invalid']);
    }

    public static function reportTestResultValidationInvalidDataProvider(): array
    {
        $validPayload = self::getRequestPayloadForTestResult();
        $faker = Factory::create('nl_NL');

        $person = $validPayload['person'];
        $personAddress = $validPayload['person']['address'];
        $triage = $validPayload['triage'];
        $test = $validPayload['test'];

        return [
            'empty' => [[]],
            'orderId missing' => [collect($validPayload)->forget('orderId')->toArray()],
            'orderId null' => [collect($validPayload)->put('orderId', null)->toArray()],
            'orderId int' => [collect($validPayload)->put('orderId', $faker->randomNumber())->toArray()],
            'messageId missing' => [collect($validPayload)->forget('messageId')->toArray()],
            'messageId null' => [collect($validPayload)->put('messageId', null)->toArray()],
            'messageId string' => [collect($validPayload)->put('messageId', $faker->word())->toArray()],
            'ggdIdentifier missing' => [collect($validPayload)->forget('ggdIdentifier')->toArray()],
            'ggdIdentifier null' => [collect($validPayload)->put('ggdIdentifier', null)->toArray()],
            'ggdIdentifier string' => [collect($validPayload)->put('ggdIdentifier', $faker->word())->toArray()],
            'person missing' => [collect($validPayload)->forget('person')->toArray()],
            'person null' => [collect($validPayload)->put('person', null)->toArray()],
            'person string' => [collect($validPayload)->put('person', $faker->word())->toArray()],
            'person.initials missing' => [collect($validPayload)->put('person', collect($person)->forget('surname'))->toArray()],
            'person.firstname missing' => [collect($validPayload)->put('person', collect($person)->forget('firstName'))->toArray()],
            'person.surname null' => [collect($validPayload)->put('person', collect($person)->put('surname', null))->toArray()],
            'person.bsn missing' => [collect($validPayload)->put('person', collect($person)->forget('bsn'))->toArray()],
            'person.bsn string' => [collect($validPayload)->put('person', collect($person)->put('bsn', $faker->word()))->toArray()],
            'person.dateOfBirth missing' => [collect($validPayload)->put('person', collect($person)->forget('dateOfBirth'))->toArray()],
            'person.dateOfBirth null' => [collect($validPayload)->put('person', collect($person)->put('dateOfBirth', null))->toArray()],
            'person.dateOfBirth invalid' => [collect($validPayload)->put('person', collect($person)->put('dateOfBirth', '30-12-2000'))->toArray()],
            'person.gender missing' => [collect($validPayload)->put('person', collect($person)->forget('gender'))->toArray()],
            'person.gender invalid' => [collect($validPayload)->put('person', collect($person)->put('gender', $faker->word()))->toArray()],
            'person.email missing' => [collect($validPayload)->put('person', collect($person)->forget('email'))->toArray()],
            'person.telephoneNumber missing' => [collect($validPayload)->put('person', collect($person)->forget('telephoneNumber'))->toArray()],
            'person.address missing' => [collect($validPayload)->put('person', collect($person)->forget('address'))->toArray()],
            'person.address null' => [collect($validPayload)->put('person', collect($person)->put('address', null))->toArray()],
            'person.address string' => [collect($validPayload)->put('person', collect($person)->put('address', $faker->word()))->toArray()],
            'person.address.streetName' => [collect($validPayload)->put('person', collect($person)->put('address', collect($personAddress)->forget('streetName')))->toArray()],
            'person.address.houseNumber' => [collect($validPayload)->put('person', collect($person)->put('address', collect($personAddress)->forget('houseNumber')))->toArray()],
            'person.address.houseNumberSuffix' => [collect($validPayload)->put('person', collect($person)->put('address', collect($personAddress)->forget('houseNumberSuffix')))->toArray()],
            'person.address.postcode' => [collect($validPayload)->put('person', collect($person)->put('address', collect($personAddress)->forget('postcode')))->toArray()],
            'person.address.city' => [collect($validPayload)->put('person', collect($person)->put('address', collect($personAddress)->forget('city')))->toArray()],
            'triage missing' => [collect($validPayload)->forget('triage')->toArray()],
            'triage null' => [collect($validPayload)->put('triage', null)->toArray()],
            'triage string' => [collect($validPayload)->put('triage', $faker->word())->toArray()],
            'triage.dateOfFirstSymptom missing' => [collect($validPayload)->put('triage', collect($triage)->forget('dateOfFirstSymptom'))->toArray()],
            'triage.dateOfFirstSymptom invalid format' => [collect($validPayload)->put('triage', collect($triage)->put('dateOfFirstSymptom', '30-12-2000'))->toArray()],
            'test missing' => [collect($validPayload)->forget('test')->toArray()],
            'test null' => [collect($validPayload)->put('test', null)->toArray()],
            'test string' => [collect($validPayload)->put('test', $faker->word())->toArray()],
            'test.sampleDate missing' => [collect($validPayload)->put('test', collect($test)->forget('sampleDate'))->toArray()],
            'test.sampleDate null' => [collect($validPayload)->put('test', collect($test)->put('sampleDate', null))->toArray()],
            'test.sampleDate invalid format' => [collect($validPayload)->put('test', collect($test)->put('sampleDate', $faker->word()))->toArray()],
            'test.resultDate missing' => [collect($validPayload)->put('test', collect($test)->forget('resultDate'))->toArray()],
            'test.resultDate null' => [collect($validPayload)->put('test', collect($test)->put('resultDate', null))->toArray()],
            'test.resultDate invalid format' => [collect($validPayload)->put('test', collect($test)->put('resultDate', $faker->word()))->toArray()],
            'test.sampleLocation missing' => [collect($validPayload)->put('test', collect($test)->forget('sampleLocation'))->toArray()],
            'test.sampleId missing' => [collect($validPayload)->put('test', collect($test)->forget('sampleId'))->toArray()],
            'test.typeOfTest missing' => [collect($validPayload)->put('test', collect($test)->forget('typeOfTest'))->toArray()],
            'test.typeOfTest invalid' => [collect($validPayload)->put('test', collect($test)->put('typeOfTest', $faker->word()))->toArray()],
            'test.result missing' => [collect($validPayload)->put('test', collect($test)->forget('result'))->toArray()],
            'test.result null' => [collect($validPayload)->put('test', collect($test)->put('result', null))->toArray()],
            'test.result invalid' => [collect($validPayload)->put('test', collect($test)->put('result', $faker->word()))->toArray()],
            'test.source missing' => [collect($validPayload)->put('test', collect($test)->forget('source'))->toArray()],
            'test.source null' => [collect($validPayload)->put('test', collect($test)->put('source', null))->toArray()],
            'test.source invalid' => [collect($validPayload)->put('test', collect($test)->put('source', $faker->word()))->toArray()],
            'test.testLocation missing' => [collect($validPayload)->put('test', collect($test)->forget('testLocation'))->toArray()],
            'test.testLocationCategory missing' => [collect($validPayload)->put('test', collect($test)->forget('testLocationCategory'))->toArray()],
        ];
    }

    public function testBadRequestForInvalidPayloadClaim(): void
    {
        /** @var string $jwtSecret */
        $jwtSecret = config('services.jwt.secret');

        $token = JWT::encode(['invalid payload'], $jwtSecret, 'HS256', 'ggdghor');

        $response = $this->json(
            Request::METHOD_POST,
            'api/v1/test-results',
            [],
            ['Authorization' => sprintf('Bearer %s', $token)],
        );
        $response->assertStatus(400);
        $response->assertJsonFragment(['Invalid JWT payload']);
    }

    public function testExtraFieldsAreNotPassed(): void
    {
        $extraFieldInPayload = $this->faker->word();

        $testResultReportService = Mockery::mock(
            TestResultReportService::class,
            static function (MockInterface $mock) use ($extraFieldInPayload): void {
                $mock->expects('save')
                    ->withArgs(function (string $messageId, array $args) use ($extraFieldInPayload): bool {
                        // make sure extra field is not passed to the save method
                        return !array_key_exists($extraFieldInPayload, $args);
                    });
            }
        );
        $this->app->instance(TestResultReportService::class, $testResultReportService);

        $payload = self::getRequestPayloadForTestResult();
        $payload[$extraFieldInPayload] = $this->faker->word();

        $this->doRequest($payload);
    }

    public function testAuditService(): void
    {
        $messageId = $this->faker->bothify('#########');
        $payload = self::getRequestPayloadForTestResult();
        $payload['messageId'] = $messageId;
        $auditEvent = Mockery::mock(AuditEvent::class);
        $auditObject = null;

        $this->mock(AuditService::class, function (Mockery\MockInterface $mock) use ($auditEvent, &$auditObject): void {
            $mock->expects('startEvent')
                ->andReturn($auditEvent);

            $auditEvent->expects('object')
                ->withArgs(function (AuditObject $arg) use (&$auditObject): bool {
                    $auditObject = $arg;
                    $this->assertEquals('processTestResultReport', $arg->getType());

                    return true;
                });

            $mock->expects('finalizeHttpEvent');
            $mock->expects('isEventExpected')
                ->andReturnTrue();
            $mock->expects('isEventRegistered')
                ->andReturnTrue();
        });

        $this->doRequest($payload);

        $this->assertEquals($auditObject?->getIdentifier(), $messageId);
    }


    private function doRequest(array $payload): TestResponse
    {
        /** @var string $jwtSecret */
        $jwtSecret = config('services.jwt.secret');

        $token = JWT::encode(['http://ggdghor.nl/payload' => json_encode($payload)], $jwtSecret, 'HS256', 'ggdghor');

        return $this->json(
            Request::METHOD_POST,
            'api/v1/test-results',
            [],
            ['Authorization' => sprintf('Bearer %s', $token)],
        );
    }
}
