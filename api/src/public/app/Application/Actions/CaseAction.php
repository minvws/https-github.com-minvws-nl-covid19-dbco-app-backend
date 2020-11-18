<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Responses\CaseResponse;
use DBCO\PublicAPI\Application\Services\CaseService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Actions\ValidationError;
use DBCO\Shared\Application\Actions\ValidationException;
use DBCO\Shared\Application\Models\SealedData;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List case specific tasks.
 *
 * @package DBCO\PublicAPI\Application\Actions
 */
class CaseAction extends Action
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

        $token = $this->args['token'] ?? null;
        if (empty($token)) {
            $errors[] = ValidationError::url('isRequired', 'token is required', 'token');
        }

        if (count($errors) > 0) {
            throw new ValidationException($this->request, $errors);
        }

        $case = $this->caseService->getCase($token);

        // always return a result so it is not visible if a case exists or not
        if ($case === null) {
            $case = new SealedData(
                random_bytes(rand(512, 4096)),
                random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES)
            );
        }

        return $this->respond(new CaseResponse($case));
    }
}
