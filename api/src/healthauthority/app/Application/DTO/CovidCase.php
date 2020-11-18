<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DateTime;
use DateTimeZone;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase as CovidCaseModel;
use JsonSerializable;
use stdClass;

/**
 * Case export DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class CovidCase implements JsonSerializable
{
    /**
     * @var CovidCaseModel $case
     */
    private CovidCaseModel $case;

    /**
     * Constructor.
     *
     * @param CovidCase $case
     */
    public function __construct(CovidCaseModel $case)
    {
        $this->case = $case;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'windowExpiresAt' =>
                $this->case->windowExpiresAt !== null ?
                    $this->case->windowExpiresAt
                        ->setTimezone(new DateTimeZone('UTC'))
                        ->format('Y-m-d\TH:i:s\Z') : null,
            'dateOfSymptomOnset' =>
                $this->case->dateOfSymptomOnset !== null ?
                    $this->case->dateOfSymptomOnset->format('Y-m-d') : null,
            'tasks' => array_map(fn ($t) => new Task($t), $this->case->tasks)
        ];
    }


    /**
     * Unserialize from JSON data structure.
     *
     * @param stdClass $data
     *
     * @return CovidCaseModel
     */
    public static function jsonUnserialize(stdClass $data): CovidCaseModel
    {
        $case = new CovidCaseModel();

        $case->uuid = $data->uuid ?? null;

        $tz = new DateTimeZone('UTC');

        $windowExpiresAt = $data->windowExpiresAt ?? null;
        $case->windowExpiresAt =
            $windowExpiresAt !== null ?
                DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $windowExpiresAt, $tz) : null;

        $dateOfSymptomOnset = $data->dateOfSymptomOnset ?? null;
        $case->dateOfSymptomOnset =
            $dateOfSymptomOnset !== null ? DateTime::createFromFormat('Y-m-d', $dateOfSymptomOnset) : null;

        $tasks = $data->tasks ?? [];
        $case->tasks = array_map(fn ($t) => Task::jsonUnserialize($t), $tasks);

        return $case;
    }
}
