<?php
namespace DBCO\HealthAuthorityAPI\Application\Services;

use DBCO\HealthAuthorityAPI\Application\DTO\CovidCase as CovidCaseDTO;
use DBCO\HealthAuthorityAPI\Application\DTO\Task as TaskDTO;
use DBCO\HealthAuthorityAPI\Application\DTO\QuestionnaireResult as QuestionnaireResultDTO;
use DBCO\HealthAuthorityAPI\Application\DTO\Answer as AnswerDTO;
use DBCO\HealthAuthorityAPI\Application\Helpers\EncryptionHelper;
use DBCO\HealthAuthorityAPI\Application\Models\Answer;
use DBCO\HealthAuthorityAPI\Application\Models\Client;
use DBCO\HealthAuthorityAPI\Application\Models\ClientRegistration;
use DBCO\HealthAuthorityAPI\Application\Models\CovidCase;
use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireResult;
use DBCO\HealthAuthorityAPI\Application\Models\Task;
use DBCO\HealthAuthorityAPI\Application\Models\TaskList;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseExportRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\ClientRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\GeneralTaskRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\QuestionnaireRepository;
use DBCO\Shared\Application\Codable\JSONDecoder;
use DBCO\Shared\Application\Managers\TransactionManager;
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
     * @var QuestionnaireRepository
     */
    private QuestionnaireRepository $questionnaireRepository;

    /**
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    /**
     * @var TransactionManager
     */
    private TransactionManager $transactionManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GeneralTaskRepository   $generalTaskRepository
     * @param CaseRepository          $caseRepository
     * @param ClientRepository        $clientRepository
     * @param CaseExportRepository    $caseExportRepository
     * @param QuestionnaireRepository $questionnaireRepository
     * @param EncryptionHelper        $encryptionHelper
     * @param TransactionManager      $transactionManager
     * @param LoggerInterface         $logger
     */
    public function __construct(
        GeneralTaskRepository $generalTaskRepository,
        CaseRepository $caseRepository,
        ClientRepository $clientRepository,
        CaseExportRepository $caseExportRepository,
        QuestionnaireRepository $questionnaireRepository,
        EncryptionHelper $encryptionHelper,
        TransactionManager $transactionManager,
        LoggerInterface $logger
    )
    {
        $this->generalTaskRepository = $generalTaskRepository;
        $this->caseRepository = $caseRepository;
        $this->clientRepository = $clientRepository;
        $this->caseExportRepository = $caseExportRepository;
        $this->questionnaireRepository = $questionnaireRepository;
        $this->encryptionHelper = $encryptionHelper;
        $this->transactionManager = $transactionManager;
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
        $json = json_encode(new CovidCaseDTO($case));
        $sealedCase = $this->encryptionHelper->sealMessageForClient($json, $client->transmitKey);
        $this->caseExportRepository->exportCase($client->token, $sealedCase, $case->windowExpiresAt);
    }

    /**
     * Export case for all paired clients.
     *
     * @param string $caseUuid
     *
     * @throws CaseNotFoundException
     */
    public function exportCase(string $caseUuid)
    {
        $case = $this->caseRepository->getCase($caseUuid);
        if ($case === null) {
            throw new CaseNotFoundException('Case does not exist!');
        }

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
     * @return ClientRegistration Client registration data.
     *
     * @throws CaseNotFoundException
     */
    public function registerClient(string $caseUuid, string $sealedClientPublicKey): ClientRegistration
    {
        $caseExists = $this->caseRepository->caseExists($caseUuid);
        if (!$caseExists) {
            throw new CaseNotFoundException('Case does not exist!');
        }

        $uuid = Uuid::uuid4()->toString();
        $clientPublicKey = $this->encryptionHelper->unsealClientPublicKey($sealedClientPublicKey);
        $healthAuthorityKeyPair = $this->encryptionHelper->createHealthAuthorityKeyPair();
        $healthAuthorityPublicKey = $this->encryptionHelper->getHealthAuthorityPublicKey($healthAuthorityKeyPair);
        $sealedHealthAuthorityPublicKey = $this->encryptionHelper->sealHealthAuthorityPublicKeyForClient($healthAuthorityPublicKey, $clientPublicKey);
        [$receiveKey, $transmitKey] = $this->encryptionHelper->deriveSharedSecretKeys($healthAuthorityKeyPair, $clientPublicKey);
        $token = $this->encryptionHelper->deriveSharedToken($receiveKey, $transmitKey);

        $client =
            new Client(
                $uuid,
                $caseUuid,
                $token,
                $receiveKey,
                $transmitKey
            );

        $case = $this->caseRepository->getCase($caseUuid);
        $this->clientRepository->registerClient($client, $case->windowExpiresAt);
        $this->caseRepository->markCaseAsPaired($caseUuid);
        $this->exportCaseForClient($case, $client);

        return new ClientRegistration($client, $sealedHealthAuthorityPublicKey);
    }


    /**
     * Submit case with tasks.
     *
     * @param string $token Case token.
     * @param SealedData $sealedCase Sealed case data.
     *
     * @throws CaseNotFoundException
     * @throws SealedBoxException
     * @throws Exception
     */
    public function submitCase(string $token, SealedData $sealedCase): void
    {
        $client = $this->clientRepository->getClient($token);
        if ($client === null) {
            throw new CaseNotFoundException('Case does not exist!'); // TODO: different error?
        }

        $json = $this->encryptionHelper->unsealMessageFromClient($sealedCase, $client->receiveKey);
        if (empty($json)) {
            throw new SealedBoxException();
        }

        $decoder = new JSONDecoder();

        $decoder->getContext()->registerDecorator(CovidCase::class, CovidCaseDTO::class);
        $decoder->getContext()->registerDecorator(Task::class, TaskDTO::class);
        $decoder->getContext()->registerDecorator(QuestionnaireResult::class, QuestionnaireResultDTO::class);
        $decoder->getContext()->registerDecorator(Answer::class, AnswerDTO::class);

        $questionnaireList = $this->questionnaireRepository->getQuestionnaires();
        $questionnaires =
            array_combine(
                array_map(fn ($q) => $q->uuid, $questionnaireList->questionnaires),
                $questionnaireList->questionnaires
            );
        $decoder->getContext()->setValue('questionnaires', $questionnaires);

        /** @var $case CovidCase */
        $case = $decoder->decode($json)->decodeObject(CovidCase::class);
        $case->uuid = $client->caseUuid;

        $this->transactionManager->run(function () use ($case) {
            $this->caseRepository->storeCaseAnswers($case);
        });
    }
}
