<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Responses\TaskListResponse;
use App\Application\Services\TaskService;
use DBCO\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List general tasks.
 *
 * @package App\Application\Actions
 */
class GeneralTaskListAction extends Action
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
        $tasks = $this->taskService->getGeneralTasks();
        return $this->respond(new TaskListResponse($tasks));
    }
}
