<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Exceptions\PairingRequestExpiredException;
use DBCO\PublicAPI\Application\Exceptions\PairingRequestNotFoundException;
use DBCO\PublicAPI\Application\Responses\PairingResponse;
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
     * Validate contents of parsed body.
     *
     * @param mixed $body
     *
     * @throws ValidationException
     */
    private function validateBody($body)
    {
        $errors = [];

        if (!is_array($body)) {
            $errors[] = ValidationError::body('invalid', 'body should be a valid JSON object string', []);
        } else {
            if (empty($body['pairingCode'])) {
                $errors[] = ValidationError::body('isRequired', 'pairingCode is required', ['pairingCode']);
            } else if (!is_string($body['pairingCode'])) {
                $errors[] = ValidationError::body('invalid', 'pairingCode should be a string', ['pairingCode']);
            }

            if (empty($body['sealedClientPublicKey'])) {
                $errors[] = ValidationError::body('isRequired', 'sealedClientPublicKey is required', ['sealedClientPublicKey']);
            } else if (!is_string($body['sealedClientPublicKey'])) {
                $errors[] = ValidationError::body('invalid', 'sealedClientPublicKey should be a string', ['sealedClientPublicKey']);
            } else if (base64_decode($body['sealedClientPublicKey'], true) === false) { // not bulletproof
                $errors[] = ValidationError::body('invalid', 'sealedClientPublicKey is not a valid base64 encoded string', ['sealedClientPublicKey']);
            }
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $body = $this->request->getParsedBody();

        $this->validateBody($body);

        $pairingCode = $body['pairingCode'];
        $sealedClientPublicKey = base64_decode($body['sealedClientPublicKey']);

        try {
            $pairing = $this->pairingService->completePairing($pairingCode, $sealedClientPublicKey);
            return $this->respond(new PairingResponse($pairing));
        } catch (PairingRequestNotFoundException $e) {
            $error = ValidationError::body('invalid', 'Invalid pairing code', ['pairingCode']);
            throw new ValidationException($this->request, [$error]);
        } catch (PairingRequestExpiredException $e) {
            $error = ValidationError::body('expired', 'Expired pairing code', ['pairingCode']);
            throw new ValidationException($this->request, [$error]);
        }
    }
}
