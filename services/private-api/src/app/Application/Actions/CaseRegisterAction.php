<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Application\Actions;

use DateTime;
use DBCO\PrivateAPI\Application\Helpers\JWTConfigHelper;
use DBCO\PrivateAPI\Application\Responses\CaseRegisterResponse;
use DBCO\PrivateAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Register a new DBCO case.
 *
 * @package DBCO\PrivateAPI\Application\Actions
 */
class CaseRegisterAction extends Action
{
    private const CLAIM_CASE_UUID = 'http://ggdghor.nl/caseUuid';

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
        $claimCaseUuid = null;
        if ($this->jwtConfigHelper->isEnabled()) {
            $jwtClaims = $this->request->getAttribute("jwtClaims");
            $claimCaseUuid = $jwtClaims[self::CLAIM_CASE_UUID];
        }

        $body = $this->request->getParsedBody();

        $errors = [];

        $caseUuid = $body['caseUuid'] ?? null;
        if (empty($caseUuid)) {
            $errors[] = ValidationError::body('isRequired', 'caseUuid is required', ['caseUuid']);
        } else if ($this->jwtConfigHelper->isEnabled() && $caseUuid !== $claimCaseUuid) {
            $errors[] = ValidationError::body('invalid', 'caseUuid does not match claim', ['caseUuid']);
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $pairingRequest = $this->caseService->registerCase($caseUuid);
        return $this->respond(new CaseRegisterResponse($pairingRequest));
    }
}
