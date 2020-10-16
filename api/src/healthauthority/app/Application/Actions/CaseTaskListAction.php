<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Responses\TaskListResponse;
use App\Application\Services\TaskService;
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
class CaseTaskListAction extends Action
{
    /**
     * @var TaskService
     */
    protected TaskService $taskService;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param TaskService     $taskService
     */
    public function __construct(
        LoggerInterface $logger,
        TaskService $taskService
    )
    {
        parent::__construct($logger);
        $this->taskService = $taskService;
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

        $infection = $this->taskService->getCaseTasks($caseId);

        return $this->respond(new InfectionResponse($infection));
    }
}
