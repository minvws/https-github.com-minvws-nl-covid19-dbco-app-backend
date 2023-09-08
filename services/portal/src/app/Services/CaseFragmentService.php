<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Config;
use App\Models\Eloquent\EloquentBaseModel;
use App\Models\Eloquent\EloquentCase;
use App\Repositories\CaseFragmentRepository;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Exception;
use Webmozart\Assert\Assert;

use function count;
use function sprintf;

class CaseFragmentService extends AbstractFragmentService implements CaseFragmentsValidationService
{
    private const FRAGMENT_NAMES = [
        'general',
        'index',
        'contact',
        'alternateContact',
        'alternativeLanguage',
        'deceased',
        'symptoms',
        'test',
        'vaccination',
        'hospital',
        'underlyingSuffering',
        'pregnancy',
        'recentBirth',
        'medication',
        'generalPractitioner',
        'alternateResidency',
        'housemates',
        'riskLocation',
        'job',
        'eduDaycare',
        'principalContextualSettings',
        'abroad',
        'contacts',
        'groupTransport',
        'sourceEnvironments',
        'communication',
        'immunity',
        'extensiveContactTracing',
    ];

    public function __construct(
        private readonly CaseFragmentRepository $fragmentRepository,
    ) {
    }

    protected static function fragmentNamespace(): string
    {
        return 'App\Models\CovidCase';
    }

    /**
     * @inheritDoc
     */
    public static function fragmentNames(): array
    {
        return self::FRAGMENT_NAMES;
    }

    /**
     * @inheritDoc
     */
    public function loadFragments(string $ownerUuid, array $fragmentNames, bool $includeSoftDeletes = false, bool $disableAuthFilter = false): array
    {
        return $this->fragmentRepository->loadCaseFragments($ownerUuid, $fragmentNames, $includeSoftDeletes, $disableAuthFilter);
    }

    /**
     * @inheritDoc
     */
    public function storeFragments(string $ownerUuid, array $fragments): void
    {
        $this->fragmentRepository->storeCaseFragments($ownerUuid, $fragments);
    }

    /**
     * @throws Exception
     */
    public function validateAllFragments(
        EloquentCase $case,
        array $filterTags = [],
        bool $stopOnFirstFailedSeverityLevel = true,
    ): array {
        $loadedFragments = $this->loadFragments(
            $case->uuid,
            self::fragmentNames(),
            true,
            true,
        );
        $encodedFragments = $this->encodeFragments($loadedFragments);

        $validatedData = [];
        $validatedFragments = $this->validateFragments(
            $case,
            self::fragmentNames(),
            $encodedFragments,
            $validatedData,
            $filterTags,
            $stopOnFirstFailedSeverityLevel,
        );

        $validationResult = [];
        foreach ($validatedFragments as $fragmentName => $validatedFragment) {
            if ($validatedFragment !== []) {
                $validationResult[$fragmentName] = $validatedFragment;
            }
        }

        return $validationResult;
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalValidationData(EloquentBaseModel $owner, array $fragmentData): array
    {
        Assert::isInstanceOf($owner, EloquentCase::class);

        if (!$this->caseValidationDataIsCached($owner->uuid)) {
            $this->cachedAdditionalValidationData['caseUuid'] = $owner->uuid;
            $this->cachedAdditionalValidationData['caseCreationDate'] = $this->formatDate($owner->created_at);

            $this->cachedAdditionalValidationData['index-dateOfBirth'] = $this->formatDate($owner->index->dateOfBirth);
            $this->cachedAdditionalValidationData['test-dateOfSymptomOnset'] = $this->formatDate($owner->test->dateOfSymptomOnset);
            $this->cachedAdditionalValidationData['test-dateOfTest'] = $this->formatDate($owner->test->dateOfTest);
            $this->cachedAdditionalValidationData['test-dateOfResult'] = $this->formatDate($owner->test->dateOfResult);
            $this->cachedAdditionalValidationData['test-previousInfectionDateOfSymptom'] = $this->formatDate(
                $owner->test->previousInfectionDateOfSymptom,
            );
            $this->cachedAdditionalValidationData['hospital-admittedInICUAt'] = $this->formatDate($owner->hospital->admittedInICUAt);
            $this->cachedAdditionalValidationData['hospital-admittedAt'] = $this->formatDate($owner->hospital->admittedAt);
            $this->cachedAdditionalValidationData['deceased-isDeceased'] = $owner->deceased->isDeceased;
            $this->cachedAdditionalValidationData['deceased-deceasedAt'] = $this->formatDate($owner->deceased->deceasedAt);
            $this->cachedAdditionalValidationData['underlyingSuffering-hasUnderlyingSufferingOrMedication'] = $owner->underlying_suffering->hasUnderlyingSufferingOrMedication?->value;
            $this->cachedAdditionalValidationData['underlyingSuffering-hasUnderlyingSuffering'] = $owner->underlying_suffering->hasUnderlyingSuffering?->value;

            $this->cachedAdditionalValidationData['maxBeforeCaseCreationDate'] = $this->subDateAndFormat(
                $owner->created_at,
                'maxBeforeCaseCreationDateInDays',
                'days',
            );
            $this->cachedAdditionalValidationData['maxDueDateBeforeCaseCreation'] = $this->subDateAndFormat(
                $owner->created_at,
                'maxDueDateBeforeCaseCreationInDays',
                'days',
            );
            $this->cachedAdditionalValidationData['maxDueDateAfterCaseCreation'] = $this->addDateAndFormat(
                $owner->created_at,
                'maxDueDateAfterCaseCreationInMonths',
                'months',
            );
            $this->cachedAdditionalValidationData['maxRecentBirthBeforeCaseCreation'] = $this->subDateAndFormat(
                $owner->created_at,
                'maxRecentBirthBeforeCaseCreationInWeeks',
                'weeks',
            );
            $this->cachedAdditionalValidationData['maxAbroadDepartureBeforeCaseCreation'] = $this->subDateAndFormat(
                $owner->created_at,
                'maxAbroadDepartureBeforeCaseCreationInYears',
                'years',
            );
            $this->cachedAdditionalValidationData['startOfCovidSurveillanceDate'] = Config::string(
                'misc.validations.startOfCovidSurveillanceDate',
            );
            $this->cachedAdditionalValidationData['firstReportedCovidCaseDate'] = Config::string(
                'misc.validations.firstReportedCovidCaseDate',
            );
            $this->cachedAdditionalValidationData['firstAllowableDateOfSymptomOnset'] = Config::string(
                'misc.validations.firstAllowableDateOfSymptomOnset',
            );
            $this->cachedAdditionalValidationData['isCareProfessional'] = $owner->index->isCareProfessional();
        }

        return $this->cachedAdditionalValidationData;
    }

    private function caseValidationDataIsCached(string $ownerUuid): bool
    {
        if (count($this->cachedAdditionalValidationData) === 0) {
            return false;
        }

        return $this->cachedAdditionalValidationData['caseUuid'] === $ownerUuid;
    }

    private function addDateAndFormat(DateTimeInterface $date, string $configName, string $subUnit): ?string
    {
        $subValue = Config::integer(sprintf('misc.validations.%s', $configName));

        $date = new CarbonImmutable($date);
        $addDate = $date->add(sprintf('%s %s', $subValue, $subUnit));

        return $this->formatDate($addDate);
    }

    private function subDateAndFormat(DateTimeInterface $date, string $configName, string $subUnit): ?string
    {
        $subValue = Config::integer(sprintf('misc.validations.%s', $configName));

        $date = new CarbonImmutable($date);
        $subDate = $date->sub(sprintf('%s %s', $subValue, $subUnit));

        return $this->formatDate($subDate);
    }

    private function formatDate(?DateTimeInterface $date): ?string
    {
        return $date?->format('Y-m-d');
    }
}
