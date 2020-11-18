<?php
namespace DBCO\PublicAPI\Application\Responses;

use DBCO\PublicAPI\Application\Models\SealedCase;
use DBCO\Shared\Application\Responses\Response;
use JsonSerializable;

/**
 * Case task list response.
 */
class CaseResponse extends Response implements JsonSerializable
{
    /**
     * @var SealedCase
     */
    private SealedCase $case;

    /**
     * Constructor.
     *
     * @param SealedCase $case
     */
    public function __construct(SealedCase $case)
    {
        $this->case = $case;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'sealedCase' => [
                'ciphertext' => base64_encode($this->case->ciphertext),
                'nonce' => base64_encode($this->case->nonce)
            ]
        ];
    }
}
