<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Responses\PairingResponse;
use DBCO\PublicAPI\Application\Services\InvalidPairingCodeException;
use DBCO\PublicAPI\Application\Services\PairingService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Link device to case.
 *
 * @package DBCO\PublicAPI\Application\Actions
 */
class PairingAction extends Action
{
    /**
     * @var PairingService
     */
    protected PairingService $pairingService;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param PairingService  $pairingService
     */
    public function __construct(
        LoggerInterface $logger,
        PairingService $pairingService
    )
    {
        parent::__construct($logger);
        $this->pairingService = $pairingService;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $body = $this->request->getParsedBody();

        $errors = [];

        $pairingCode = $body['pairingCode'] ?? null;
        if (empty($pairingCode)) {
            $errors[] = ValidationError::body('isRequired', 'pairingCode is required', ['pairingCode']);
        }

        $deviceType = $body['deviceType'] ?? null;
        if (empty($deviceType)) {
            $errors[] = ValidationError::body('isRequired', 'deviceType is required', ['deviceType']);
        }

        $deviceName = $body['deviceName'] ?? null;
        if (empty($deviceName)) {
            $errors[] = ValidationError::body('isRequired', 'deviceName is required', ['deviceName']);
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        try {
            $pairing = $this->pairingService->completePairing($pairingCode, $deviceType, $deviceName);
            return $this->respond(new PairingResponse($pairing));
        } catch (InvalidPairingCodeException $e) {
            $error = ValidationError::body('invalid', 'Invalid or expired pairing code', ['pairingCode']);
            throw new ValidationException($this->request, [$error]);
        }
    }
}
