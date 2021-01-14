<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Application\Actions;

use DateTime;
use DBCO\PrivateAPI\Application\Helpers\JWTConfigHelper;
use DBCO\PrivateAPI\Application\Responses\CaseUpdateResponse;
use DBCO\PrivateAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use DBCO\Shared\Application\Models\SealedData;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Store case details for client retrieval.
 *
 * @package DBCO\PrivateAPI\Application\Actions
 */
class CaseUpdateAction extends Action
{
    private const CLAIM_TOKEN = 'http://ggdghor.nl/token';

    /**
     * @var CaseService
     */
    protected CaseService $caseService;

    /**
     * @var JWTConfigHelper
     */
    private JWTConfigHelper $jwtConfigHelper;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param CaseService     $caseService
     * @param JWTConfigHelper $jwtConfigHelper
     */
    public function __construct(
        LoggerInterface $logger,
        CaseService $caseService,
        JWTConfigHelper $jwtConfigHelper
    )
    {
        parent::__construct($logger);
        $this->caseService = $caseService;
        $this->jwtConfigHelper = $jwtConfigHelper;
    }

    /**
     * Validate body.
     *
     * @param mixed $body
     *
     * @param array $errors
     */
    private function validateBody($body, array &$errors)
    {
        if (!is_array($body)) {
            $errors[] = ValidationError::body('invalid', 'Body should contain a valid JSON string', []);
            return;
        }

        $sealedCase = $body['sealedCase'] ?? null;
        if (empty($sealedCase)) {
            $errors[] = ValidationError::body('isRequired', 'sealedCase is required', ['sealedCase']);
        } else {
            if (empty($sealedCase['ciphertext'])) {
                $errors[] = ValidationError::body('isRequired', 'sealedCase.ciphertext is required', ['sealedCase', 'ciphertext']);
            }

            if (empty($sealedCase['nonce'])) {
                $errors[] = ValidationError::body('isRequired', 'sealedCase.nonce is required', ['sealedCase', 'nonce']);
            }
        }

        if (empty($body['expiresAt'])) {
            $errors[] = ValidationError::body('isRequired', 'expiresAt is required', ['expiresAt']);
        } else {
            try {
                $expiresAt = new DateTime($body['expiresAt']);

                if ($expiresAt < new DateTime()) {
                    $errors[] = ValidationError::body('invalid', 'expiresAt is in the past', ['expiresAt']);
                }
            } catch (Exception $e) {
                $errors[] = ValidationError::body('invalid', 'expiresAt is invalid', ['expiresAt']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $claimToken = null;
        if ($this->jwtConfigHelper->isEnabled()) {
            $jwtClaims = $this->request->getAttribute("jwtClaims");
            $claimToken = $jwtClaims[self::CLAIM_TOKEN];
        }

        $errors = [];

        $token = $this->args['token'] ?? null;
        if (empty($token)) {
            $errors = ValidationError::url('isRequired', 'token is required', 'token');
        } else if ($this->jwtConfigHelper->isEnabled() && $token !== $claimToken) {
            $errors[] = ValidationError::url('invalid', 'token does not match claim', 'token');
        }

        $body = $this->request->getParsedBody();
        $this->validateBody($body, $errors);

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $expiresAt = new DateTime($body['expiresAt']);
        $sealedCase = new SealedData(
            base64_decode($body['sealedCase']['ciphertext']),
            base64_decode($body['sealedCase']['nonce'])
        );

        $this->caseService->storeCase($token, $sealedCase, $expiresAt);

        return $this->respond(new CaseUpdateResponse());
    }
}
