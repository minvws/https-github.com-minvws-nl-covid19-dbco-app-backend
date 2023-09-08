<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use App\Dto\Osiris\Client\Credentials;
use App\Models\CovidCase\Communication;
use App\Models\CovidCase\Hospital;
use App\Models\CovidCase\Trip;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Osiris\CredentialsRepository;
use App\Services\Osiris\Answer\NCOVVast1eziektedagBuilder;
use App\Services\Osiris\Answer\Utils;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use App\Services\Osiris\SoapMessage\SoapMessageBuilderFactory;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Support\Facades\App;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\OsirisAssertElement;

use function now;

#[Group('osiris')]
class SoapMessageBuilderTest extends FeatureTestCase
{
    use OsirisAssertElement;

    private const CASE_VERSION = 5;

    private SoapMessageBuilderFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = App::get(SoapMessageBuilderFactory::class);

        $this->mock(CredentialsRepository::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('getForOrganisation')
                ->andReturn(
                    new Credentials('sysLogin', 'sysPassword', 'userLogin'),
                );
        });
    }

    public function testConvertCaseToOsirisXML(): void
    {
        $body = $this->factory->build($this->createOsirisCase())
            ->makeSoapMessage($this->faker->randomElement(CaseExportType::cases()))
            ->getBody();

        $this->assertRootElement('melding', $body);
    }

    public function testWasAbroadSingleExport(): void
    {
        $case = $this->createOsirisCase();

        /** @var Trip $trip */
        $trip = $case->abroad->getSchemaVersion()->getExpectedField('trips')->newInstance();
        $trip->countries = [Country::nld()];
        $trip->departureDate = $this->faker->dateTimeBetween('-4 weeks', '-2 weeks');
        $trip->returnDate = $this->faker->dateTimeBetween('-5 days', 'now');

        $abroad = $case->abroad;
        $abroad->wasAbroad = YesNoUnknown::yes();
        $abroad->trips = [$trip];

        $body = $this->factory->build($case)
            ->makeSoapMessage(CaseExportType::INITIAL_ANSWERS)
            ->getBody();

        $this->assertAnswerElement('MELGGDOntvDt', Utils::formatDate($case->created_at), $body)
            ->assertAnswerElement('PATGeslacht', 'M', $body)
            ->assertAnswerElementNotExists('MERSPATbuitenl2', $body)
            ->assertElement('meld_nummer', $case->case_id, $body)
            ->assertElement('status_code', 'A2FIAT', $body);
    }

    public function testCaseIdNull(): void
    {
        $case = $this->createOsirisCase(withHpZone: true);

        $body = $this->factory->build($case)
            ->makeSoapMessage($this->faker->randomElement(CaseExportType::cases()))
            ->getBody();

        $this->assertElement('meld_nummer', (string) $case->hpzone_number, $body);
    }

    public function testWasAbroadMultiExport(): void
    {
        $case = $this->createOsirisCase();

        /** @var Trip $trip */
        $trip = $case->abroad->getSchemaVersion()->getExpectedField('trips')->newInstance();
        $trip->countries = [Country::nld()];
        $trip->departureDate = $this->faker->dateTimeBetween('-4 weeks', '-2 weeks');
        $trip->returnDate = $this->faker->dateTimeBetween('-5 days', 'now');

        /** @var Trip $trip2 */
        $trip2 = $case->abroad->getSchemaVersion()->getExpectedField('trips')->newInstance();
        $trip2->countries = [Country::swe()];
        $trip2->departureDate = $this->faker->dateTimeBetween('-4 weeks', '-2 weeks');
        $trip2->returnDate = $this->faker->dateTimeBetween('-5 days', 'now');

        $abroad = $case->abroad;
        $abroad->wasAbroad = YesNoUnknown::yes();
        $abroad->trips = [$trip, $trip2];

        $body = $this->factory->build($case)
            ->makeSoapMessage(CaseExportType::INITIAL_ANSWERS)
            ->getBody();


        $this->assertAnswerElement('MELGGDOntvDt', Utils::formatDate($case->created_at), $body)
            ->assertAnswerElement('PATGeslacht', 'M', $body)
            ->assertElement('meld_nummer', $case->case_id, $body)
            ->assertElement('status_code', 'A2FIAT', $body);
    }

    public function testIndexInZHS(): void
    {
        $case = $this->createOsirisCase();

        $case->hospital = Hospital::getSchema()->getCurrentVersion()->newInstance();
        $case->hospital->reason = HospitalReason::covid();
        $case->hospital->isAdmitted = YesNoUnknown::yes();
        $case->hospital->admittedAt = $this->faker->dateTimeBetween('-2 days', 'now');

        $body = $this->factory->build($case)
            ->makeSoapMessage($this->faker->randomElement([CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS]))
            ->getBody();

        $this->assertAnswerElement('NCOVpatZhs', 'J', $body)
            ->assertAnswerElement('NCOVpatZhsInd', '1', $body)
            ->assertAnswerElement('NCOVdat1ezkhopn', Utils::formatDate($case->hospital->admittedAt), $body);
    }

    public function testZiekenhuisExportAndersCloseContact(): void
    {
        $case = $this->createOsirisCase();

        $case->job->professionOther = ProfessionOther::kapper();
        $case->job->wasAtJob = YesNoUnknown::yes();
        $case->job->sectors = [JobSector::andereBeroep()];
        $case->job->closeContactAtJob = YesNoUnknown::yes();

        $body = $this->factory->build($case)
            ->makeSoapMessage($this->faker->randomElement([CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS]))
            ->getBody();

        $this->assertAnswerElement('MELGGDOntvDt', now()->format('d-m-Y'), $body)
            ->assertAnswerElementNotExists('NCOVwerkzorgber', $body); //removed in v10
    }

    public function testZiekenhuisExportZorgmedewerker(): void
    {
        $case = $this->createOsirisCase();

        $case->job->professionCare = ProfessionCare::verpleegkundige();
        $case->job->wasAtJob = YesNoUnknown::yes();
        $case->job->sectors = [JobSector::ziekenhuis()];

        $body = $this->factory->build($case)
            ->makeSoapMessage($this->faker->randomElement([CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS]))
            ->getBody();

        $this->assertAnswerElement('MELGGDOntvDt', now()->format('d-m-Y'), $body)
            ->assertAnswerElementNotExists('MELGGDExtern', $body)
            ->assertAnswerElement('PATGeslacht', 'M', $body)
            ->assertAnswerElement('NCOVgebdat', $case->index->dateOfBirth->format('d-m-Y'), $body)
            ->assertAnswerElement('NCOVVast1eziektedag', NCOVVast1eziektedagBuilder::UNKNOWN, $body)
            ->assertAnswerElement('NCOVtypeTest', '4', $body)
            ->assertAnswerElement('NCOVgezstat', '6', $body);
    }

    public function testIndexInZhsICU(): void
    {
        $case = $this->createOsirisCase();

        $case->hospital = Hospital::getSchema()->getCurrentVersion()->newInstance();
        $case->hospital->reason = HospitalReason::covid();
        $case->hospital->isAdmitted = YesNoUnknown::yes();
        $case->hospital->admittedAt = $this->faker->dateTimeBetween('-5 days', 'now');
        $case->hospital->isInICU = YesNoUnknown::yes();
        $case->hospital->admittedInICUAt = $this->faker->dateTimeBetween('-2 days', 'now');

        $body = $this->factory->build($case)
            ->makeSoapMessage($this->faker->randomElement([CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS]))
            ->getBody();

        $this->assertAnswerElement('NCOVpatZhs', 'J', $body)
            ->assertAnswerElement('NCOVpatZhsInd', '1', $body)
            ->assertAnswerElement('NCOVdat1ezkhopn', Utils::formatDate($case->hospital->admittedAt), $body)
            ->assertAnswerElement('NCOVopnameICU', 'J', $body)
            ->assertAnswerElement('NCOVopnamedatumICU', Utils::formatDate($case->hospital->admittedInICUAt), $body);
    }

    #[DataProvider('provideMELGGDExternBuilderScenarios')]
    public function testMELGGDExternAnswer(bool $caseDeleted, ?string $rivmRemark, ?string $expected): void
    {
        $case = $this->createOsirisCase();

        $case->deletedAt = $caseDeleted ? $this->faker->dateTime : null;
        $communication = Communication::getSchema()->getCurrentVersion()->newInstance();
        $communication->remarksRivm = $rivmRemark;
        $case->communication = $communication;

        $body = $this->factory->build($case)
            ->makeSoapMessage(CaseExportType::DELETED_STATUS)
            ->getBody();

        $expected === null
            ? $this->assertAnswerElementNotExists('MELGGDExtern', $body)
            : $this->assertAnswerElement('MELGGDExtern', $expected, $body);
    }

    public static function provideMELGGDExternBuilderScenarios(): Generator
    {
        yield 'deleted case with RIVM remark' => [true, 'remark', 'remark'];
        yield 'deleted case without RIVM remark' => [true, null, 'Verwijderd'];
        yield 'existing case with RIVM remark' => [false, 'remark', 'remark'];
        yield 'existing case without RIVM remark' => [false, null, null];
    }

    #[DataProvider('provideCaseExportTypesWithDeleteMissingAnswersValue')]
    public function testDeleteMissingAnswerBasedOnCaseExportType(
        CaseExportType $caseExportType,
        string $deleteMissingAnswersValue,
    ): void {
        $body = $this->factory->build($this->createOsirisCase())
            ->makeSoapMessage($caseExportType)
            ->getBody();

        $this->assertElement('wis_missend_antwoord', $deleteMissingAnswersValue, $body);
    }

    public static function provideCaseExportTypesWithDeleteMissingAnswersValue(): Generator
    {
        yield 'case export type `INITIAL_ANSWERS`' => [CaseExportType::INITIAL_ANSWERS, 'true'];
        yield 'case export type `DEFINITIVE_ANSWERS`' => [CaseExportType::DEFINITIVE_ANSWERS, 'true'];
        yield 'case export type `DELETED_STATUS`' => [CaseExportType::DELETED_STATUS, 'false'];
    }

    #[DataProvider('provideCaseExportTypesWithStatusCodes')]
    public function testStatusCodeBasedOnCaseExportType(
        CaseExportType $caseExportType,
        string $statusCode,
    ): void {
        $body = $this->factory->build($this->createOsirisCase())
            ->makeSoapMessage($caseExportType)
            ->getBody();

        $this->assertElement('status_code', $statusCode, $body);
    }

    public static function provideCaseExportTypesWithStatusCodes(): Generator
    {
        yield 'case export type `INITIAL_ANSWERS`' => [CaseExportType::INITIAL_ANSWERS, SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL];
        yield 'case export type `DEFINITIVE_ANSWERS`' => [CaseExportType::DEFINITIVE_ANSWERS, SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE];
        yield 'case export type `DELETED_STATUS`' => [CaseExportType::DELETED_STATUS, SoapMessageBuilder::NOTIFICATION_STATUS_DELETED];
    }

    private function createOsirisCase(bool $withHpZone = false): EloquentCase
    {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion(self::CASE_VERSION)->newInstance();

        $case->uuid = Uuid::uuid4()->toString();
        $withHpZone
            ? $case->hpzone_number = $this->faker->randomNumber(6)
            : $case->case_id = $this->faker->randomNumber(6);
        $case->createdAt = CarbonImmutable::now();

        $index = $case->index;
        $index->gender = Gender::male();
        $index->firstname = $this->faker->name();
        $index->lastname = $this->faker->name();
        $index->dateOfBirth = $this->faker->dateTimeBetween('-80 years', '-2 weeks');

        $case->organisation = $this->createOrganisation();

        return $case;
    }
}
