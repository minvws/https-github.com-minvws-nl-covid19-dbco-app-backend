<?php
namespace App\Application\Responses;

use App\Application\DTO\Task;
use App\Application\Models\Infection;
use DBCO\Application\Responses\Response;
use JsonSerializable;

/**
 * Infection response.
 */
class InfectionResponse extends Response implements JsonSerializable
{
    /**
     * @var Infection
     */
    private Infectio $infection;

    /**
     * Constructor.
     *
     * @param Infection $infection
     */
    public function __construct(Infection $infection)
    {
       $this->infection = $infection;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'dateOfSymptomOnset' => $this->infection->dateOfSymptomOnset,
            'tasks' => array_map(fn ($t) => new Task($t), $this->infection->tasks)
        ];
    }
}
