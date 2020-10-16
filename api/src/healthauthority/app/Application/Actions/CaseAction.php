<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Responses\CaseResponse;
use App\Application\Services\CaseService;
use DBCO\Application\Actions\Action;
use DBCO\Application\Actions\ValidationError;
use DBCO\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List case specific tasks.
 *
 * @package App\Application\Actions
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
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $case = $this->caseService->getCase($caseId);

        return $this->respond(new CaseResponse($case));
    }
}
