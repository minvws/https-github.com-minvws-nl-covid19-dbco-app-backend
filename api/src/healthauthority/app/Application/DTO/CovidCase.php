<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DateTimeZone;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase as CovidCaseModel;
use DBCO\HealthAuthorityAPI\Application\Models\Task as TaskModel;
use DBCO\Shared\Application\Codable\DecodableDecorator;
use DBCO\Shared\Application\Codable\DecodingContainer;
use JsonSerializable;

/**
 * Case export DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class CovidCase implements JsonSerializable, DecodableDecorator
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
     * @inheritDoc
     */
    public static function decode(string $class, DecodingContainer $container): object
    {
        $case = new CovidCaseModel();

        $tz = new DateTimeZone('UTC');
        $case->windowExpiresAt =
            $container->windowExpiresAt->decodeDateTimeIfPresent('Y-m-d\TH:i:s\Z', $tz);

        $case->dateOfSymptomOnset =
            $container->dateOfSymptomOnset->decodeDateTimeIfPresent('Y-m-d');

        $case->tasks =
            $container->tasks->decodeArray(fn (DecodingContainer $c) => $c->decodeObject(TaskModel::class));

        return $case;
    }
}
