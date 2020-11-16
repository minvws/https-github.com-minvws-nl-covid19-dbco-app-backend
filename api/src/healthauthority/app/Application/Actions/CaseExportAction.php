<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Responses\CaseExportResponse;
use DBCO\HealthAuthorityAPI\Application\Services\CaseNotFoundException;
use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ActionException;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Export case to the sluice.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Actions
 */
class CaseExportAction extends Action
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
        $errors = [];

        $caseUuid = $this->args['caseUuid'] ?? null;
        if (empty($caseUuid)) {
            $errors = ValidationError::url('isRequired', 'caseUuid is required', ['$caseUuid']);
        }

        $body = (string)$this->request->getBody();
        if (!empty($body)) {
            $errors[] = ValidationError::body('invalid', 'body should be empty', []);
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        try {
            $this->caseService->exportCase($caseUuid);
        } catch (CaseNotFoundException $e) {
            throw new ActionException($this->request, 'caseNotFoundError', $e->getMessage(), ActionException::NOT_FOUND);
        }

        return $this->respond(new CaseExportResponse());
    }
}
