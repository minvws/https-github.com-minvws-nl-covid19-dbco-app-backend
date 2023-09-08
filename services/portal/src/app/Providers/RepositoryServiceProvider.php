<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\GgdSocCrypter;
use App\Repositories\AnswerRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\CallToActionNoteRepository;
use App\Repositories\CallToActionRepository;
use App\Repositories\CaseAssignmentHistoryRepository;
use App\Repositories\CaseFragmentRepository;
use App\Repositories\CaseMetricsRepository;
use App\Repositories\CaseOsirisNotificationRepository;
use App\Repositories\CaseRepository;
use App\Repositories\CaseStatusHistoryRepository;
use App\Repositories\CaseStatusRepository;
use App\Repositories\ChoreRepository;
use App\Repositories\ConfigPermissionRepository;
use App\Repositories\ContextFragmentRepository;
use App\Repositories\ContextRepository;
use App\Repositories\DbAnswerRepository;
use App\Repositories\DbAssignmentRepository;
use App\Repositories\DbCallToActionNoteRepository;
use App\Repositories\DbCallToActionRepository;
use App\Repositories\DbCaseAssignmentHistoryRepository;
use App\Repositories\DbCaseFragmentRepository;
use App\Repositories\DbCaseMetricsRepository;
use App\Repositories\DbCaseOsirisNotificationRepository;
use App\Repositories\DbCaseRepository;
use App\Repositories\DbCaseStatusHistoryRepository;
use App\Repositories\DbCaseStatusRepository;
use App\Repositories\DbChoreRepository;
use App\Repositories\DbContextFragmentRepository;
use App\Repositories\DbContextRepository;
use App\Repositories\DbMessageRepository;
use App\Repositories\DbMomentRepository;
use App\Repositories\DbOrganisationRepository;
use App\Repositories\DbPlaceCountersRepository;
use App\Repositories\DbPlaceRepository;
use App\Repositories\DbQuestionnaireRepository;
use App\Repositories\DbQuestionRepository;
use App\Repositories\DbSearchHashCaseRepository;
use App\Repositories\DbSectionRepository;
use App\Repositories\DbTaskFragmentRepository;
use App\Repositories\DbTaskRepository;
use App\Repositories\DbUserRepository;
use App\Repositories\MessageRepository;
use App\Repositories\MomentRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\PlaceCountersRepository;
use App\Repositories\PlaceRepository;
use App\Repositories\QuestionnaireRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SearchHashCaseRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SessionStateRepository;
use App\Repositories\StateRepository;
use App\Repositories\TaskFragmentRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TestResult\DbTestResultRepository;
use App\Repositories\TestResultRepository;
use App\Repositories\UserRepository;
use DBCO\Shared\Application\Metrics\Transformers\EventTransformer;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\ServiceProvider;
use MinVWS\DBCO\Metrics\Repositories\DbTaskProgressRepository;
use MinVWS\DBCO\Metrics\Repositories\EventDbStorageRepository;
use MinVWS\DBCO\Metrics\Repositories\TaskProgressRepository;
use MinVWS\Metrics\Repositories\StorageRepository;
use MinVWS\Metrics\Transformers\EventTransformer as EventTransformerInterface;
use PDO;
use Webmozart\Assert\Assert;

class RepositoryServiceProvider extends ServiceProvider
{
    private Config $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(Config::class);
    }

    public function register(): void
    {
        $this->app->bind(UserRepository::class, DbUserRepository::class);
        $this->app->bind(StateRepository::class, SessionStateRepository::class);
        $this->app->bind(PlaceRepository::class, DbPlaceRepository::class);
        $this->app->bind(SectionRepository::class, DbSectionRepository::class);
        $this->app->bind(ContextRepository::class, DbContextRepository::class);
        $this->app->bind(MomentRepository::class, DbMomentRepository::class);
        $this->app->bind(TaskProgressRepository::class, DbTaskProgressRepository::class);
        $this->app->when(DbTaskProgressRepository::class)
            ->needs(PDO::class)
            ->give($this->getPdoFromDefaultConnection(...));
        $this->app->bind(TaskRepository::class, DbTaskRepository::class);
        $this->app->bind(QuestionnaireRepository::class, DbQuestionnaireRepository::class);
        $this->app->bind(AnswerRepository::class, DbAnswerRepository::class);
        $this->app->bind(QuestionRepository::class, DbQuestionRepository::class);
        $this->app->bind(OrganisationRepository::class, DbOrganisationRepository::class);
        $this->app->bind(CaseRepository::class, DbCaseRepository::class);
        $this->app->bind(CaseFragmentRepository::class, DbCaseFragmentRepository::class);
        $this->app->bind(CaseStatusRepository::class, DbCaseStatusRepository::class);
        $this->app->bind(ChoreRepository::class, DbChoreRepository::class);
        $this->app->bind(AssignmentRepository::class, DbAssignmentRepository::class);
        $this->app->bind(CallToActionRepository::class, DbCallToActionRepository::class);
        $this->app->bind(CallToActionNoteRepository::class, DbCallToActionNoteRepository::class);
        $this->app->bind(ContextFragmentRepository::class, DbContextFragmentRepository::class);
        $this->app->bind(TaskFragmentRepository::class, DbTaskFragmentRepository::class);
        $this->app->bind(MessageRepository::class, DbMessageRepository::class);
        $this->app->bind(PermissionRepository::class, ConfigPermissionRepository::class);
        $this->app->bind(CaseAssignmentHistoryRepository::class, DbCaseAssignmentHistoryRepository::class);
        $this->app->bind(TestResultRepository::class, DbTestResultRepository::class);
        $this->app->bind(CaseStatusHistoryRepository::class, DbCaseStatusHistoryRepository::class);
        $this->app->bind(CaseMetricsRepository::class, DbCaseMetricsRepository::class);
        $this->app->bind(PlaceCountersRepository::class, DbPlaceCountersRepository::class);
        $this->app->bind(SearchHashCaseRepository::class, DbSearchHashCaseRepository::class);

        $this->app->bind(StorageRepository::class, EventDbStorageRepository::class);
        $this->app->when(EventDbStorageRepository::class)
            ->needs(PDO::class)
            ->give($this->getPdoFromDefaultConnection(...));

        $this->app->bind(EventTransformerInterface::class, EventTransformer::class);
        $this->app->when(EventTransformer::class)
            ->needs(PDO::class)
            ->give($this->getPdoFromDefaultConnection(...));


        $this->app->bind(GgdSocCrypter::class, function (): GgdSocCrypter {
            /** @var string $senderSecretKey */
            $senderSecretKey = $this->config->get('ggdsoc.sender_secret_key');
            Assert::string($senderSecretKey);

            /** @var string $receiverPublicKey */
            $receiverPublicKey = $this->config->get('ggdsoc.receiver_public_key');
            Assert::string($receiverPublicKey);

            return GgdSocCrypter::withBase64Keys($senderSecretKey, $receiverPublicKey);
        });

        $this->app->bind(CaseOsirisNotificationRepository::class, DbCaseOsirisNotificationRepository::class);
    }

    private function getPdoFromDefaultConnection(Application $app): PDO
    {
        /** @var ConnectionResolverInterface $db */
        $db = $app->make(ConnectionResolverInterface::class);

        /** @var Connection $connection */
        $connection = $db->connection();
        Assert::isInstanceOf($connection, Connection::class);

        return $connection->getPdo();
    }
}
