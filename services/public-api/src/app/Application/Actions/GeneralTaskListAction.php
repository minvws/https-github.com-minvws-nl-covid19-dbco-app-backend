<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Responses\GeneralTaskListResponse;
use DBCO\PublicAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List general tasks.
 *
 * @package DBCO\PublicAPI\Application\Actions
 */
class GeneralTaskListAction extends Action
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
        $tasks = $this->caseService->getGeneralTasks();
        return $this->respond(new GeneralTaskListResponse($tasks));
    }
}
