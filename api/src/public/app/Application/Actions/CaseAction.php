<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Responses\CaseResponse;
use DBCO\PublicAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List case specific tasks.
 *
 * @package DBCO\PublicAPI\Application\Actions
 */
class CaseAction extends Action
{
    /**
     * @var CaseService
     */
    protected CaseService $caseService;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param TaskService     $taskService
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
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $case = $this->caseService->getCase($caseId);

        return $this->respond(new CaseResponse($case));
    }
}
