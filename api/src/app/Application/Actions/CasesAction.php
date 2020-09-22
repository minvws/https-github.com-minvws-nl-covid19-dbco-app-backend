<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\DTO\DbcoCase;
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
        $parsedBody = $this->request->getParsedBody();
        $caseId = $parsedBody['caseId'] ?? null;
        if ($caseId === null) {
            $this->logger->error('No caseId found in the request data');
            $this->response->getBody()->write(
                json_encode(new ActionError(ActionError::BAD_REQUEST, 'No caseId found in the request data'))
            );
            return $this->response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }


        $case = $this->caseService->create($caseId);
        $this->response->getBody()->write(json_encode(new DbcoCase($case)));
        return $this->response->withHeader('Content-Type', 'application/json');
    }
}
