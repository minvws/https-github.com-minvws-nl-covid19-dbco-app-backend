<?php
namespace DBCO\Worker\Application\Exceptions;

use DBCO\Worker\Application\Models\PairingRequest;
use Exception;

/**
 * Exception during pairing process.
 */
class PairingException extends Exception
{
    /**
     * @var PairingRequest
     */
    private PairingRequest $request;

    /**
     * Constructor.
     *
     * @param string         $message
     * @param PairingRequest $request
     */
    public function __construct(string $message, PairingRequest $request)
    {
        parent::__construct($message);
        $this->request = $request;
    }

    /**
     * Returns the pairing request.
     *
     * @return PairingRequest
     */
    public function getRequest(): PairingRequest
    {
        return $this->request;
    }
}