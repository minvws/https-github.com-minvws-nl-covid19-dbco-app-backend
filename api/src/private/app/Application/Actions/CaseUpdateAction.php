<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Application\Actions;

use DateTime;
use DBCO\PrivateAPI\Application\Helpers\JWTConfigHelper;
use DBCO\PrivateAPI\Application\Responses\CaseUpdateResponse;
use DBCO\PrivateAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Store case details for client retrieval.
 *
 * @package DBCO\PrivateAPI\Application\Actions
 */
class CaseUpdateAction extends Action
{
    private const CLAIM_TOKEN = 'http://ggdghor.nl/token';

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
        $claimToken = null;
        if ($this->jwtConfigHelper->isEnabled()) {
            $jwtClaims = $this->request->getAttribute("jwtClaims");
            $claimToken = $jwtClaims[self::CLAIM_TOKEN];
        }

        $errors = [];

        $token = $this->args['token'] ?? null;
        if (empty($token)) {
            $errors = ValidationError::url('isRequired', 'token is required', 'token');
        } else if ($this->jwtConfigHelper->isEnabled() && $token !== $claimToken) {
            $errors[] = ValidationError::body('invalid', 'token does not match claim', ['token']);
        }

        $rawExpiresAt = $this->request->getHeaderLine('Expires');
        if (empty($rawExpiresAt)) {
            $errors[] = ValidationError::header('isRequired', 'Expires header is required', 'Expires');
        } else {
            try {
                $expiresAt = new DateTime($rawExpiresAt);

                if ($expiresAt < new DateTime()) {
                    $errors[] = ValidationError::header('invalid', 'Expires header is in the past', 'Expires');
                }
            } catch (Exception $e) {
                $errors[] = ValidationError::header('invalid', 'Expires header is invalid', 'Expires');
            }
        }

        if (count($errors) > 0 || !isset($expiresAt)) {
            throw new ValidationException($this->request, $errors);
        }

        $payload = $this->request->getBody()->getContents();

        $this->caseService->storeCase($token, $payload, $expiresAt);

        return $this->respond(new CaseUpdateResponse());
    }
}
