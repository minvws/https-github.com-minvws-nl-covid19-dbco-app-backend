<?php
namespace App\Application\Services;

use App\Application\Models\DbcoCase;
use App\Application\Repositories\CaseRepository;
use Exception;
use Psr\Log\LoggerInterface;

class CaseService
{
    /**
     * @var CaseRepository
     */
    private CaseRepository $caseRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param CaseRepository $caseRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CaseRepository $caseRepository,
        LoggerInterface $logger
    )
    {
        $this->caseRepository = $caseRepository;
        $this->logger = $logger;
    }

    /**
     * Run the case.
     *
     * @return Case
     *
     * @throws Exception
     */
    public function create(): DbcoCase
    {
        $this->logger->debug('Create case');
        
        $case = $this->caseRepository->create();

        return $case;
    }
}
