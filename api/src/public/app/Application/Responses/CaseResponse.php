<?php
namespace DBCO\PublicAPI\Application\Responses;

use DBCO\Shared\Application\DTO\SealedData as SealedDataDTO;
use DBCO\Shared\Application\Models\SealedData;
use DBCO\Shared\Application\Responses\Response;
use JsonSerializable;

/**
 * Case task list response.
 */
class CaseResponse extends Response implements JsonSerializable
{
    /**
     * @var SealedData
     */
    private SealedData $case;

    /**
     * Constructor.
     *
     * @param SealedData $case
     */
    public function __construct(SealedData $case)
    {
        $this->case = $case;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'sealedCase' => new SealedDataDTO($this->case)
        ];
    }
}
