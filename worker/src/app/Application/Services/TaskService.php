<?php
namespace App\Application\Services;

use App\Application\Models\GeneralTaskList;
use App\Application\Repositories\GeneralTaskCacheRepository;
use App\Application\Repositories\GeneralTaskGetRepository;
use Exception;
use Psr\Log\LoggerInterface;

class TaskService
{
    /**
     * @var GeneralTaskGetRepository
     */
    private GeneralTaskGetRepository $generalTaskGetRepository;

    /**
     * @var GeneralTaskCacheRepository
     */
    private GeneralTaskCacheRepository $generalTaskCacheRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GeneralTaskGetRepository   $generalTaskGetRepository
     * @param GeneralTaskCacheRepository $generalTaskCacheRepository
     * @param LoggerInterface            $logger
     */
    public function __construct(
        GeneralTaskGetRepository $generalTaskGetRepository,
        GeneralTaskCacheRepository $generalTaskCacheRepository,
        LoggerInterface $logger
    )
    {
        $this->generalTaskGetRepository = $generalTaskGetRepository;
        $this->generalTaskCacheRepository = $generalTaskCacheRepository;
        $this->logger = $logger;
    }

    /**
     * Retrieve a fresh list of general tasks.
     *
     * @return GeneralTaskList
     *
     * @throws Exception
     */
    private function getGeneralTasks(): GeneralTaskList
    {
        return $this->generalTaskGetRepository->getGeneralTasks();
    }

    /**
     * Store the general task list in the cache.
     *
     * @param GeneralTaskList $tasks
     *
     * @throws Exception
     */
    private function cacheGeneralTasks(GeneralTaskList $tasks)
    {
        $this->generalTaskCacheRepository->putGeneralTasks($tasks);
    }

    /**
     * Refresh the general tasks cache.
     *
     * @throws Exception
     */
    public function refreshGeneralTasks()
    {
        $this->logger->debug('Refreshing general tasks cache');

        try {
            $tasks = $this->getGeneralTasks();
            $this->cacheGeneralTasks($tasks);

            $this->logger->debug('Successfully refreshed general tasks cache');
        } catch (Exception $e) {
            $this->logger->error('Error refreshing general tasks cache: ' . $e->getMessage());
            $this->logger->debug($e->getTraceAsString());
            throw $e;
        }
    }
}
