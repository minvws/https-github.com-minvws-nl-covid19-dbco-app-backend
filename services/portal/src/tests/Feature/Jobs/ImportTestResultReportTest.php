<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Exceptions\OrganisationNotFoundException;
use App\Exceptions\TestResultReport\CouldNotDecodePayload;
use App\Exceptions\TestResultReport\CouldNotDecryptPayload;
use App\Jobs\ImportTestResultReport;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Person;
use App\Models\Eloquent\TestResult;
use App\Models\Metric\TestResult\FailureReason;
use App\Models\TestResult\General;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\CaseLabelRepository;
use App\Repositories\Metric\MetricRepository;
use App\Services\TestResult\TestResultReportImportServiceInterface;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use Exception;
use MinVWS\DBCO\Encryption\Security\SecurityCache;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use MinVWS\DBCO\Enum\Models\TestResultType;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;
use Mockery;
use Mockery\MockInterface;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

use function base64_encode;
use function json_encode;
use function sodium_crypto_box_publickey_from_secretkey;
use function sodium_crypto_box_seal;

use const DATE_ATOM;
use const JSON_THROW_ON_ERROR;

final class ImportTestResultReportTest extends FeatureTestCase
{
    public function testSkipTestResultImportIfOrganisationIsNotAllowed(): void
    {
        $payload = TestResultDataProvider::payload();
        $organisation = $this->createOrganisation([
            'is_allowed_to_report_test_results' => false,
            'hp_zone_code' => $payload['ggdIdentifier'],
        ]);

        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));
        $this->app->call([$importTestResultReport, 'handle']);

        $this->assertDatabaseMissing('test_result', ['organisation_uuid' => $organisation->uuid]);
    }

    public function testMessageIsUnrecoverableWhenOrganisationCouldNotBeFound(): void
    {
        $ggdIdentifier = 'unknownGgdIdentifier';
        $payload = TestResultDataProvider::payload($ggdIdentifier);

        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));

        $this->expectException(OrganisationNotFoundException::class);
        $this->app->call([$importTestResultReport, 'handle']);

        $this->assertDatabaseMissing('organisation', ['hp_zone_code' => $payload['ggdIdentifier']]);
    }

    public function testImportTestResultAndCovidCaseWhenIdentificationSucceeds(): void
    {
        $receivedAt = new DateTimeImmutable('2021-11-22 08:06:28');

        $payload = TestResultDataProvider::payload();
        $payload['receivedAt'] = $receivedAt->format(DATE_ATOM);

        $organisation = $this->createOrganisation([
            'is_allowed_to_report_test_results' => true,
            'hp_zone_code' => $payload['ggdIdentifier'],
        ]);

        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));
        $this->app->call([$importTestResultReport, 'handle']);

        $testResult = TestResult::where('message_id', $payload['messageId'])->first();
        $this->assertInstanceOf(TestResult::class, $testResult);
        $this->assertEquals(TestResultSource::coronit()->value, $testResult->source);
        $this->assertEquals($organisation->uuid, $testResult->organisation->uuid);
        $this->assertEquals('000A0000000', $testResult->monsterNumber);
        $this->assertEquals(new DateTime('2021-01-19 00:00:00'), $testResult->dateOfTest);
        $this->assertEquals(new DateTime('2021-01-05 00:00:00'), $testResult->dateOfSymptomOnset);
        $this->assertSame(TestResultType::lab(), $testResult->type);
        $this->assertSame(TestResultSource::coronit(), $testResult->source);
        $this->assertEquals('000A0000000', $testResult->sourceId);
        $this->assertEquals($receivedAt, $testResult->receivedAt);
        $this->assertNotEmpty($testResult->person->pseudoBsnGuid);
        $this->assertNotEquals($testResult->person->pseudoBsnGuid, $payload['person']['bsn']);
        $this->assertInstanceOf(Person::class, $testResult->person);
        $this->assertInstanceOf(EloquentOrganisation::class, $testResult->organisation);
        $this->assertInstanceOf(General::class, $testResult->general);
        $this->assertEquals(TestResultTypeOfTest::unknown(), $testResult->type_of_test);
    }

    public function testImportTestResultAndAssignToExistingCovidCase(): void
    {
        $payload = TestResultDataProvider::payload();
        $covidCase = $this->createCase([
            'pseudo_bsn_guid' => '1eaf0d45-1124-4799-931d-58f628635079',
            'created_at' => CarbonImmutable::today()->subWeeks(4),
        ]);
        $this->createOrganisation([
            'is_allowed_to_report_test_results' => true,
            'hp_zone_code' => $payload['ggdIdentifier'],
        ]);

        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));
        $this->app->call([$importTestResultReport, 'handle']);

        $testResult = TestResult::where('message_id', $payload['messageId'])->first();
        $this->assertInstanceOf(TestResult::class, $testResult);
        $this->assertInstanceOf(EloquentCase::class, $testResult->covidCase);
        $this->assertEquals($covidCase->uuid, $testResult->covidCase->uuid);
    }

    public function testIgnoreRetransmitOfSameMessage(): void
    {
        $payload = TestResultDataProvider::payload();

        $this->createOrganisation([
            'is_allowed_to_report_test_results' => true,
            'hp_zone_code' => $payload['ggdIdentifier'],
        ]);

        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));
        $this->app->call([$importTestResultReport, 'handle']);

        $this->assertDatabaseHas('test_result', ['message_id' => $payload['messageId']]);

        // retransmit message
        $this->app->call([$importTestResultReport, 'handle']);
        $this->assertDatabaseCount('test_result', 1);
    }

    public function testImportTestResultAndCovidCaseWithCaseLabelWhenIdentificationFails(): void
    {
        $payload = TestResultDataProvider::payload();

        $this->createOrganisation([
            'is_allowed_to_report_test_results' => true,
            'hp_zone_code' => $payload['ggdIdentifier'],
        ]);

        $this->mock(
            BsnRepository::class,
            static function (MockInterface $mock): void {
                $mock->expects('convertBsnAndDateOfBirthToPseudoBsn')
                    ->andThrows(new BsnException('fail'));
            },
        );

        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));
        $this->app->call([$importTestResultReport, 'handle']);

        $testResult = TestResult::all()->last();
        $this->assertInstanceOf(TestResult::class, $testResult);
        $this->assertEquals($payload['orderId'], $testResult->sourceId);
        $this->assertInstanceOf(EloquentCase::class, $testResult->covidCase);

        $caseLabel = CaseLabel::where('code', CaseLabelRepository::CASE_LABEL_CODE_NOT_IDENTIFIED)->firstOrFail();
        $this->assertDatabaseHas('case_case_label', [
            'case_uuid' => $testResult->covidCase->uuid,
            'case_label_uuid' => $caseLabel->uuid,
        ]);
    }

    public function testImportTestResultAndCovidCaseWithCaseLabelWhenBsnNull(): void
    {
        $payload = TestResultDataProvider::payload();
        $payload['person']['bsn'] = null;

        $this->createOrganisation([
            'is_allowed_to_report_test_results' => true,
            'hp_zone_code' => $payload['ggdIdentifier'],
        ]);

        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));
        $this->app->call([$importTestResultReport, 'handle']);

        /** @var TestResult $testResult */
        $testResult = TestResult::all()->last();

        $caseLabel = CaseLabel::where('code', CaseLabelRepository::CASE_LABEL_CODE_NOT_IDENTIFIED)->firstOrFail();
        $this->assertDatabaseHas('case_case_label', [
            'case_uuid' => $testResult->covidCase->uuid,
            'case_label_uuid' => $caseLabel->uuid,
        ]);
    }

    public function testHandleFailureReasonMetricWhenImportOfTestResultReportFails(): void
    {
        $exception = new Exception();

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (FailureReason $failureReason): bool {
                    return $failureReason->getLabels() === ['failureReason' => 'unknown'];
                }));
        });

        $this->mock(
            TestResultReportImportServiceInterface::class,
            static function (MockInterface $mock) use ($exception): void {
                $mock->expects('import')->andThrow($exception);
            },
        );

        $payload = TestResultDataProvider::payload();
        $importTestResultReport = new ImportTestResultReport($payload['messageId'], $this->encryptPayload($payload));

        $this->expectException($exception::class);
        $this->app->call([$importTestResultReport, 'handle']);
    }

    public function testCouldNotDecodePayload(): void
    {
        $messageId = $this->faker->numerify;

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (FailureReason $failureReason): bool {
                    return $failureReason->getLabels() === ['failureReason' => 'message_decoding_failed'];
                }));
        });

        $importTestResultReport = new ImportTestResultReport($messageId, $this->encryptPayload('bad encryption'));

        $this->expectException(CouldNotDecodePayload::class);
        $this->app->call([$importTestResultReport, 'handle']);
    }

    public function testCouldNotDecryptPayload(): void
    {
        $messageId = $this->faker->numerify;

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (FailureReason $failureReason): bool {
                    return $failureReason->getLabels() === ['failureReason' => 'message_decryption_failed'];
                }));
        });

        $importTestResultReport = new ImportTestResultReport($messageId, 'bad encryption');

        $this->expectException(CouldNotDecryptPayload::class);
        $this->app->call([$importTestResultReport, 'handle']);
    }

    private function encryptPayload(mixed $payload): string
    {
        $securityCache = $this->app->get(SecurityCache::class);
        $testResultPrivateKey = $securityCache->getSecretKey(SecurityModule::SK_TEST_RESULT);

        $publicKey = sodium_crypto_box_publickey_from_secretkey($testResultPrivateKey);
        $sealed = sodium_crypto_box_seal(json_encode($payload, JSON_THROW_ON_ERROR), $publicKey);

        return base64_encode($sealed);
    }
}
