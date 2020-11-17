<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Responses\ClientRegisterResponse;
use DBCO\HealthAuthorityAPI\Application\Services\CaseNotFoundException;
use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use DBCO\HealthAuthorityAPI\Application\Services\SealedBoxException;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ActionException;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Register client.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Actions
 */
class ClientRegisterAction extends Action
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

        $caseUuid = $this->args['caseUuid'] ?? null;
        if (empty($caseUuid)) {
            $errors[] = ValidationError::url('isRequired', 'caseUuid is required', 'caseUuid');
        } else if (!Uuid::isValid($caseUuid)) {
            $errors[] = ValidationError::url('invalid', 'caseUuid should be valid UUID', 'caseUuid');
        }

        $body = $this->request->getParsedBody();
        $sealedClientPublicKey = $body['sealedClientPublicKey'] ?? null;
        if (empty($sealedClientPublicKey)) {
            $errors[] = ValidationError::body('isRequired', 'sealedClientPublicKey is required', ['sealedClientPublicKey']);
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        try {
            $client = $this->caseService->registerClient($caseUuid, base64_decode($sealedClientPublicKey));
        } catch (CaseNotFoundException $e) {
            throw new ActionException($this->request, 'caseNotFoundError', $e->getMessage(), ActionException::NOT_FOUND);
        } catch (SealedBoxException $e) {
            $errors[] = ValidationError::body('invalid', 'sealedClientPublicKey is invalid', ['sealedClientPublicKey']);
            throw new ValidationException($this->request, $errors);
        }

        return $this->respond(new ClientRegisterResponse($client));
    }
}
