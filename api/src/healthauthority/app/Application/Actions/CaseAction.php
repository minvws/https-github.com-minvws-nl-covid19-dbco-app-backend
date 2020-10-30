<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Responses\CaseResponse;
use DBCO\HealthAuthorityAPI\Application\Services\CaseNotFoundException;
use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ActionException;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * List case specific tasks.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Actions
 */
class CaseAction extends Action
{
    /**
     * @var TaskService
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

        $caseId = $this->args['caseId'] ?? null;
        if (empty($caseId)) {
            $errors[] = ValidationError::url('isRequired', 'caseId is required', 'caseId');
        } else if (!Uuid::isValid($caseId)) {
            $errors[] = ValidationError::url('invalid', 'caseId should be valid UUID', 'caseId');
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        try {
            $case = $this->caseService->getCase($caseId);
        } catch (CaseNotFoundException $e) {
            throw new ActionException($this->request, 'caseNotFoundError', $e->getMessage(), ActionException::NOT_FOUND);
        }

        return $this->respond(new CaseResponse($case));
    }
}
