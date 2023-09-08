<?php

declare(strict_types=1);

namespace App\Services\Osiris\SoapMessage;

use App\Dto\Osiris\Client\Credentials;
use App\Dto\Osiris\Client\SoapMessage;
use App\Helpers\Environment;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Osiris\CredentialsRepository;
use App\Services\Osiris\Answer\Answer;
use SimpleXMLElement;

use function assert;
use function sprintf;
use function uniqid;

final class SoapMessageBuilder
{
    private const NOTIFICATION_CODE = 'NCOV';
    public const NOTIFICATION_STATUS_INITIAL = 'A2FIAT';
    public const NOTIFICATION_STATUS_DEFINITIVE = 'A3DEF';
    public const NOTIFICATION_STATUS_DELETED = 'A14WISG';

    private SimpleXMLElement $body;
    private Credentials $credentials;

    public function __construct(
        public readonly QuestionnaireVersion $questionnaireVersion,
        private readonly EloquentCase $case,
        private readonly AnswerBuilderProvider $answerBuilderProvider,
        CredentialsRepository $credentialsRepository,
    ) {
        $this->credentials = $credentialsRepository->getForOrganisation($case->organisation);
    }

    public static function mapToStatus(CaseExportType $caseExportType): string
    {
        return match ($caseExportType) {
            CaseExportType::INITIAL_ANSWERS => self::NOTIFICATION_STATUS_INITIAL,
            CaseExportType::DEFINITIVE_ANSWERS => self::NOTIFICATION_STATUS_DEFINITIVE,
            CaseExportType::DELETED_STATUS => self::NOTIFICATION_STATUS_DELETED,
        };
    }

    public function makeSoapMessage(CaseExportType $caseExportType): SoapMessage
    {
        $this->reset();
        $this->withMetadata($caseExportType);
        $this->withOsirisLogin($this->credentials->userLogin);
        $this->withAnswers($caseExportType);

        return new SoapMessage($this->credentials, $this->body, $this->makeCommunicationId());
    }

    private function reset(): void
    {
        $newBody = new SimpleXMLElement('<melding/>');
        $newBody->addAttribute('xmlns', 'http://tempuri.org/PutMessage.xsd');

        $this->body = $newBody;
    }

    private function withMetadata(CaseExportType $caseExportType): void
    {
        $reportNumber = $this->case->getReportNumber();
        if ($reportNumber !== null) {
            $this->body->addChild('meld_nummer', $reportNumber);
        }
        $this->body->addChild('meld_code', self::NOTIFICATION_CODE);
        $this->body->addChild('vragenlijst_versie', (string) $this->questionnaireVersion->value);
        $this->body->addChild('status_code', self::mapToStatus($caseExportType));
        $this->body->addChild('meld_locatie', '');
        $this->body->addChild('wis_missend_antwoord', $caseExportType === CaseExportType::DELETED_STATUS ? 'false' : 'true');
    }

    private function withOsirisLogin(string $userLogin): void
    {
        $this->body->addChild('osiris_gebruiker_login', $userLogin);
    }

    private function withAnswers(CaseExportType $caseExportType): void
    {
        foreach ($this->answerBuilderProvider->getAll($this->questionnaireVersion, $caseExportType) as $builder) {
            $answers = $builder->build($this->case);
            foreach ($answers as $answer) {
                $this->buildAnswer($answer);
            }
        }
    }

    private function buildAnswer(Answer $answer): void
    {
        $answerElement = $this->body->addChild('antwoord');
        assert($answerElement instanceof SimpleXMLElement);

        $answerElement->addChild('vraag_code', $answer->code);
        $answerElement->addChild('antwoord_tekst', $answer->value);
    }

    private function makeCommunicationId(): string
    {
        return uniqid(sprintf('osiris-%s-', Environment::isDevelopment() ? 'dev' : 'prod'));
    }
}
