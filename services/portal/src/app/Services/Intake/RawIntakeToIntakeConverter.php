<?php

declare(strict_types=1);

namespace App\Services\Intake;

use App\Exceptions\IntakeException;
use App\Helpers\Config;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Intake;
use App\Models\Intake\RawIntake;
use App\Repositories\Intake\IntakeRepository;
use App\Repositories\OrganisationRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\DecodingContainer;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\IntakeType;
use Psr\Log\LoggerInterface;
use Throwable;

use function config;
use function is_string;
use function sprintf;
use function strlen;

class RawIntakeToIntakeConverter
{
    private IntakeRepository $intakeRepository;
    private LoggerInterface $logger;
    private OrganisationRepository $organisationRepository;

    public function __construct(
        IntakeRepository $intakeRepository,
        LoggerInterface $logger,
        OrganisationRepository $organisationRepository,
    ) {
        $this->intakeRepository = $intakeRepository;
        $this->logger = $logger;
        $this->organisationRepository = $organisationRepository;
    }

    /**
     * @throws IntakeException
     */
    public function convert(RawIntake $rawIntake): Intake
    {
        $this->logger->info(sprintf('Creating intake "%s"...', $rawIntake->getId()));

        try {
            $decoder = new Decoder();
            $identityData = $decoder->decode($rawIntake->getIdentityData());
            $intakeData = $decoder->decode($rawIntake->getIntakeData());
            $handoverData = $decoder->decode($rawIntake->getHandoverData());

            $intake = new Intake();
            $intake->uuid = $rawIntake->getId();
            $intake->organisation_uuid = $this->getOrganisationForRegionCode($identityData->ggd_region->decodeString())->uuid;
            $intake->type = IntakeType::from($rawIntake->getType());
            $intake->source = $rawIntake->getSource();
            $intake->pseudo_bsn_guid = $identityData->guid->decodeString();
            $cat1Count = $intakeData->meta->cat1Count->decodeIfPresent();
            $intake->cat1_count = is_string($cat1Count) && strlen($cat1Count) > 0 ? (int) $cat1Count : null;
            $estimatedCat2Count = $intakeData->meta->estimatedCat2Count->decodeIfPresent();
            $intake->estimated_cat2_count = is_string($estimatedCat2Count) && strlen($estimatedCat2Count) > 0
                ? (int) $estimatedCat2Count
                : null;
            $intake->date_of_symptom_onset = $intakeData->test->dateOfSymptomOnset->decodeDateTimeIfPresent('Y-m-d');
            $intake->date_of_test = $intakeData->test->dateOfTest->decodeDateTime('Y-m-d');
            $intake->received_at = CarbonImmutable::instance($rawIntake->getReceivedAt())->setTimezone(Config::string('app.timezone'));
            $intake->created_at = CarbonImmutable::now();
            $intake->pc3 = $identityData->pc3->decodeString();

            try {
                $intake->date_of_birth = $identityData->birth_date->decodeDateTime('Y-m-d');
            } catch (Throwable) {
                $year = Str::substr($rawIntake->getIdentityData()['birth_date'], 0, 4);
                $yearDate = CarbonImmutable::createFromFormat('Y', $year);
                if ($yearDate === false) {
                    throw new IntakeException('unable to determine dateOfBirth');
                }
                $intake->date_of_birth = $yearDate->startOfYear();
            }

            /** @var ?Gender $gender */
            $gender = Gender::forValueByProperty($identityData->gender->decodeString(), 'mittensCode');
            if ($gender === null) {
                throw new IntakeException('unable to determine gender');
            }
            $intake->gender = $gender;

            $this->addIdentifierToIntake($intake, $identityData, $handoverData);

            $this->intakeRepository->saveIntake($intake);

            return $intake;
        } catch (Throwable $e) {
            $this->logger->info('Creating intake failed', [
                'exceptionMessage' => $e->getMessage(),
            ]);
            throw IntakeException::fromThrowable($e);
        }
    }

    /**
     * @throws CodableException
     */
    private function addIdentifierToIntake(
        Intake $intake,
        DecodingContainer $identityData,
        DecodingContainer $handoverData,
    ): void {
        $testMonsterNumber = $handoverData->testMonsterNumber->decodeStringIfPresent();
        if (!empty($testMonsterNumber)) {
            $intake->identifier_type = 'testMonsterNumber';
            $intake->identifier = $testMonsterNumber;
            return;
        }

        $pseudoBsnGuid = $identityData->guid->decodeString();
        $intake->identifier_type = 'pseudoBsnGuid';
        $intake->identifier = $pseudoBsnGuid;
    }

    /**
     * @throws IntakeException
     */
    private function getOrganisationForRegionCode(string $regionCode): EloquentOrganisation
    {
        $forceRegionCode = config('misc.intake.force_region_code');
        if ($forceRegionCode) {
            $regionCode = $forceRegionCode;
        }

        $organisation = $this->organisationRepository->getOrganisationByExternalId($regionCode);
        if (!$organisation instanceof EloquentOrganisation) {
            $this->logger->info('No organisation found for region code', [
                'regionCode' => $regionCode,
            ]);

            throw new IntakeException('No organisation found for region code "' . $regionCode . '"');
        }

        return $organisation;
    }
}
