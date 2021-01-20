<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Responses\CaseSubmitResponse;
use DBCO\HealthAuthorityAPI\Application\Services\CaseNotFoundException;
use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use DBCO\HealthAuthorityAPI\Application\Services\SealedBoxException;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ActionException;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use DBCO\Shared\Application\DTO\SealedData as SealedDataDTO;
use DBCO\Shared\Application\Models\SealedData;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Submit case with its specific task results.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Actions
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
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $errors = [];

        $token = $this->args['token'] ?? null;
        if (empty($token)) {
            $errors = ValidationError::url('isRequired', 'token is required', 'token');
        }

        $body = $this->request->getParsedBody();

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

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        try {
            $this->caseService->submitCase($token, SealedDataDTO::jsonUnserialize($sealedCase));
        } catch (CaseNotFoundException $e) {
            throw new ActionException($this->request, 'caseNotFoundError', $e->getMessage(), ActionException::NOT_FOUND);
        } catch (SealedBoxException $e) {
            $errors[] = ValidationError::body('invalid', 'sealedCase is invalid', ['sealedCase']);
            throw new ValidationException($this->request, $errors);
        }

        return $this->respond(new CaseSubmitResponse());
    }
}
