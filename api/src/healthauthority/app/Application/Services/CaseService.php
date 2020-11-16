<?php
namespace DBCO\HealthAuthorityAPI\Application\Services;

use DBCO\HealthAuthorityAPI\Application\DTO\CaseExport;
use DBCO\HealthAuthorityAPI\Application\Helpers\EncryptionHelper;
use DBCO\HealthAuthorityAPI\Application\Models\Client;
use DBCO\HealthAuthorityAPI\Application\Models\ClientCase;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;
use DBCO\HealthAuthorityAPI\Application\Models\TaskList;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseExportRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\ClientRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\GeneralTaskRepository;
use DBCO\Shared\Application\Models\SealedData;
use Exception;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Responsible for listing tasks.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Services
 */
class CaseService
{
    /**
     * @var GeneralTaskRepository
     */
    private GeneralTaskRepository $generalTaskRepository;

    /**
     * @var CaseRepository
     */
    private CaseRepository $caseRepository;

    /**
     * @var ClientRepository
     */
    private ClientRepository $clientRepository;

    /**
     * @var CaseExportRepository
     */
    private CaseExportRepository $caseExportRepository;

    /**
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GeneralTaskRepository $generalTaskRepository
     * @param CaseRepository        $caseRepository
     * @param ClientRepository      $clientRepository
     * @param CaseExportRepository  $caseExportRepository
     * @param EncryptionHelper      $encryptionHelper
     * @param LoggerInterface       $logger
     */
    public function __construct(
        GeneralTaskRepository $generalTaskRepository,
        CaseRepository $caseRepository,
        ClientRepository $clientRepository,
        CaseExportRepository $caseExportRepository,
        EncryptionHelper $encryptionHelper,
        LoggerInterface $logger
    )
    {
        $this->generalTaskRepository = $generalTaskRepository;
        $this->caseRepository = $caseRepository;
        $this->clientRepository = $clientRepository;
        $this->caseExportRepository = $caseExportRepository;
        $this->encryptionHelper = $encryptionHelper;
        $this->logger = $logger;
    }

    /**
     * Returns the general task list.
     *
     * @return TaskList
     *
     * @throws Exception
     */
    public function getGeneralTasks(): TaskList
    {
        return $this->generalTaskRepository->getGeneralTasks();
    }

    /**
     * Export case.
     *
     * @param CovidCase $case
     * @param Client    $client
     */
    private function exportCaseForClient(CovidCase $case, Client $client)
    {
        $json = json_encode(new CaseExport($case));
        $sealedCase = $this->encryptionHelper->sealMessageForClient($json, $client->transmitKey);
        $this->caseExportRepository->exportCase($client->token, $sealedCase);
    }

    /**
     * Export case for all paired clients.
     *
     * @param string $caseUuid
     */
    private function exportCase(string $caseUuid)
    {
        $case = $this->caseRepository->getCase($caseUuid);
        $clients = $this->clientRepository->getClientsForCase($caseUuid);
        foreach ($clients as $client) {
            $this->exportCaseForClient($case, $client);
        }
    }

    /**
     * Register client for case.
     *
     * @param string $caseUuid              Case identifier.
     * @param string $sealedClientPublicKey Sealed client public key.
     *
     * @throws CaseNotFoundException
     * @throws SealedBoxException
     */
    public function registerClient(string $caseUuid, string $sealedClientPublicKey): Client
    {
        $caseExists = $this->caseRepository->caseExists($caseUuid);
        if (!$caseExists) {
            throw new CaseNotFoundException('Case does not exist!');
        }

        $clientPublicKey = $this->encryptionHelper->unsealClientPublicKey($sealedClientPublicKey);
        $healthAuthorityKeyPair = $this->encryptionHelper->createHealthAuthorityKeyPair();
        $healthAuthorityPublicKey = $this->encryptionHelper->getHealthAuthorityPublicKey($healthAuthorityKeyPair);
        $sealedHealthAuthorityPublicKey = $this->encryptionHelper->sealHealthAuthorityPublicKeyForClient($healthAuthorityPublicKey, $clientPublicKey);
        $healthAuthoritySecretKey = $this->encryptionHelper->getHealthAuthoritySecretKey($healthAuthorityKeyPair);
        [$receiveKey, $transmitKey] = $this->encryptionHelper->deriveSharedSecretKeys($healthAuthorityKeyPair, $clientPublicKey);
        $token = $this->encryptionHelper->deriveSharedToken($receiveKey, $transmitKey);

        $client =
            new Client(
                $token,
                new ClientCase($caseUuid),
                $clientPublicKey,
                $healthAuthorityPublicKey,
                $healthAuthoritySecretKey,
                $sealedHealthAuthorityPublicKey,
                $receiveKey,
                $transmitKey
            );

        $this->clientRepository->registerClient($client);

        $case = $this->caseRepository->getCase($caseUuid);
        $this->exportCaseForClient($case, $client);

        return $client;
    }


    /**
     * Submit case with tasks.
     *
     * @param string $token             Case token.
     * @param SealedData $sealedCase Sealed case data.
     *
     * @throws CaseNotFoundException
     * @throws SealedBoxException
     */
    public function submitCase(string $token, SealedData $sealedCase): void
    {
        $client = $this->clientRepository->getClient($token);
        if ($client === null) {
            throw new CaseNotFoundException('Case does not exist!'); // TODO: different error?
        }

        $data = $this->encryptionHelper->unsealMessageFromClient($sealedCase, $client->receiveKey);
        if (empty($data)) {
            throw new SealedBoxException();
        }

        // TODO: this is a dummy implementation, we still need to process the data
    }
}
