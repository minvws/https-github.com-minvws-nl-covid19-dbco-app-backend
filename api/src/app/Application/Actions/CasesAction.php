<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Services\CaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class CasesAction extends Action
{
    protected CaseService $caseService;

    /**
     * @param LoggerInterface $logger
     * @param CasesService\
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
        $case = $this->caseService->create();
        $this->response->getBody()->write(json_encode($case));
        return $this->response->withHeader('Content-Type', 'application/json');
    }
}
