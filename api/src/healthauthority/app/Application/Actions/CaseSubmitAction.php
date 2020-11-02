<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Responses\CaseSubmitResponse;
use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Submit case with its specific task results.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Actions
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

        $caseUuid = $this->args['caseUuid'] ?? null;
        if (empty($caseUuid)) {
            $errors = ValidationError::url('isRequired', 'caseUuid is required', 'caseUuid');
        }

        // TODO: verify body is not empty, signature etc.

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $this->caseService->submitCase($caseUuid, $body);

        return $this->respond(new CaseSubmitResponse());
    }
}
