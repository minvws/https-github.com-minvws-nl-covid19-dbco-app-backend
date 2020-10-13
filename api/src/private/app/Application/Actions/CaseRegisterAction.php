<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Helpers\JWTConfigHelper;
use App\Application\Responses\CaseRegisterResponse;
use App\Application\Services\CaseService;
use DateTime;
use DBCO\Application\Actions\Action;
use DBCO\Application\Actions\ValidationError;
use DBCO\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Register a new DBCO case.
 *
 * @package App\Application\Actions
 */
class CaseRegisterAction extends Action
{
    private const CLAIM_CASE_ID = 'http://ggdghor.nl/cid';

    /**
     * @var CaseService
     */
    protected CaseService $caseService;

    /**
     * @var JWTConfigHelper
     */
    private JWTConfigHelper $jwtConfigHelper;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param CaseService     $caseService
     * @param JWTConfigHelper $jwtConfigHelper
     */
    public function __construct(
        LoggerInterface $logger,
        CaseService $caseService,
        JWTConfigHelper $jwtConfigHelper
    )
    {
        parent::__construct($logger);
        $this->caseService = $caseService;
        $this->jwtConfigHelper = $jwtConfigHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $claimCaseId = null;
        if ($this->jwtConfigHelper->isEnabled()) {
            $token = $this->request->getAttribute("token");
            $claimCaseId = $token[self::CLAIM_CASE_ID];
        }

        $body = $this->request->getParsedBody();

        $errors = [];

        $caseId = $body['caseId'] ?? null;
        if (empty($caseId)) {
            $errors[] = ValidationError::body('isRequired', 'caseId is required', ['caseId']);
        } else if ($this->jwtConfigHelper->isEnabled() && $caseId !== $claimCaseId) {
            $errors[] = ValidationError::body('invalid', 'caseId does not match claim', ['caseId']);
        }

        $caseExpiresAt = $body['caseExpiresAt'] ?? null;
        if (empty($caseExpiresAt)) {
            $errors[] = ValidationError::body('isRequired', 'caseExpiresAt is required', ['caseExpiresAt']);
        } else {
            // TODO: validate timestamp format
            $caseExpiresAt = new DateTime($caseExpiresAt);
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $pairing = $this->caseService->registerCase($caseId, $caseExpiresAt);
        return $this->respond(new CaseRegisterResponse($pairing));
    }
}
