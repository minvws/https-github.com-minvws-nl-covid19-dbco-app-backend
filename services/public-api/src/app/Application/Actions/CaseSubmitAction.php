<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Responses\CaseSubmitResponse;
use DBCO\PublicAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use DBCO\Shared\Application\DTO\SealedData as SealedDataDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Submit case with specific task results.
 *
 * @package DBCO\PublicAPI\Application\Actions
 */
class CaseSubmitAction extends Action
{
    /**
     * @var CaseService
     */
    protected CaseService $caseService;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param CaseService     $caseService
     */
    public function __construct(
        LoggerInterface $logger,
        CaseService $caseService
    )
    {
        parent::__construct($logger);
        $this->caseService = $caseService;
    }


    /**
     * Validate contents of parsed body.
     *
     * @param mixed $body
     * @param array $errors
     *
     * @throws ValidationException
     */
    private function validateBody($body, &$errors)
    {
        if (!is_array($body)) {
            $errors[] = ValidationError::body('invalid', 'body should be a valid JSON object string', []);
        } else {
            if (empty($body['sealedCase'])) {
                $errors[] = ValidationError::body('isRequired', 'sealedCase is required', ['sealedCase']);
            } else if (!is_array($body['sealedCase'])) {
                $errors[] = ValidationError::body('invalid', 'sealedCase should be an object', ['sealedCase']);
            } else {
                if (empty($body['sealedCase']['ciphertext'])) {
                    $errors[] = ValidationError::body('isRequired', 'sealedCase.ciphertext is required', ['sealedCase', 'ciphertext']);
                } else if (!is_string($body['sealedCase']['ciphertext'])) {
                    $errors[] = ValidationError::body('invalid', 'sealedCase.ciphertext should be a string', ['sealedCase', 'ciphertext']);
                }

                if (empty($body['sealedCase']['nonce'])) {
                    $errors[] = ValidationError::body('isRequired', 'sealedCase.nonce is required', ['sealedCase', 'nonce']);
                } else if (!is_string($body['sealedCase']['nonce'])) {
                    $errors[] = ValidationError::body('invalid', 'sealedCase.nonce should be a string', ['sealedCase', 'nonce']);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $errors = [];

        $token = $this->args['token'] ?? null;
        if (empty($token)) {
            $errors[] = ValidationError::url('isRequired', 'token is required', 'token');
        }

        $body = $this->request->getParsedBody();
        $this->validateBody($body, $errors);

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        // we don't do anything with the result as it could
        // expose if a case exists or not
        $this->caseService->submitCase($token, SealedDataDTO::jsonUnserialize($body['sealedCase']));

        return $this->respond(new CaseSubmitResponse());
    }
}
