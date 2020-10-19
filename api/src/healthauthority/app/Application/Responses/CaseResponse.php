<?php
namespace App\Application\Responses;

use App\Application\DTO\Task;
use App\Application\Models\CovidCase;
use DBCO\Application\Responses\Response;
use JsonSerializable;

/**
 * Case response.
 */
class CaseResponse extends Response implements JsonSerializable
{
    /**
     * @var CovidCase
     */
    private CovidCase $case;

    /**
     * Constructor.
     *
     * @param Case $case
     */
    public function __construct(CovidCase $case)
    {
       $this->case = $case;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'dateOfSymptomOnset' => $this->case->dateOfSymptomOnset,
            'tasks' => array_map(fn ($t) => new Task($t), $this->case->tasks)
        ];
    }
}
