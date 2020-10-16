<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Responses\CaseSubmitResponse;
use App\Application\Services\CaseService;
use DBCO\Application\Actions\Action;
use DBCO\Application\Actions\ValidationError;
use DBCO\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Submit case with its specific task results.
 *
 * @package App\Application\Actions
 */
class CaseSubmitAction extends Action
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
        $body = (string)$this->request->getBody();

        $errors = [];

        $caseId = $this->args['caseId'] ?? null;
        if (empty($caseId)) {
            $errors = ValidationError::url('isRequired', 'caseId is required', 'caseId');
        }

        // TODO: verify body is not empty, signature etc.

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $this->caseService->submitCase($caseId, $body);

        return $this->respond(new CaseSubmitResponse());
    }
}
