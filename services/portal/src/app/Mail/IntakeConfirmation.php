<?php

declare(strict_types=1);

namespace App\Mail;

use App\Exceptions\IntakeException;
use App\Models\CovidCase\Intake\Housemates;
use App\Models\CovidCase\Intake\Job;
use App\Models\CovidCase\Intake\Pregnancy;
use App\Models\CovidCase\Intake\RecentBirth;
use App\Models\CovidCase\Intake\Symptoms;
use App\Models\CovidCase\Intake\UnderlyingSuffering;
use App\Models\Eloquent\Intake;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\IndexPolicyGuidelineProvider;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use LogicException;
use MinVWS\DBCO\Enum\Models\IntakeType;
use MinVWS\DBCO\Enum\Models\JobSectorGroup;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Propaganistas\LaravelPhone\PhoneNumber;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Throwable;
use Webmozart\Assert\Assert;

use function count;
use function sprintf;

class IntakeConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    private ?Translator $translator;

    public function __construct(
        private readonly Intake $intake,
        private readonly IndexPolicyGuidelineProvider $policyGuidelineProvider,
    ) {
    }

    /**
     * @throws IntakeException
     */
    public function build(
        ConfigRepository $config,
        LoggerInterface $logger,
        Translator $translator,
    ): self {
        $this->translator = $translator;

        $this->view('mail.intake.confirmation')
            ->locale('nl')
            ->from(
                $config->get('mail.mailers.zivver.from.address'),
                $this->intake->organisation->name,
            )
            ->subject($this->translator->get('intake.confirmation.subject'))
            ->with([
                'advices' => $this->getAdvices(
                    $this->intake->underlyingSyffering,
                    $this->intake->housemates,
                    $this->intake->symptoms,
                ),
                'additionalAdvices' => $this->getAdditionalAdvices(
                    $this->intake->type === IntakeType::selftest(),
                    $this->intake->pregnancy,
                    $this->intake->recentBirth,
                    $this->intake->housemates,
                    $this->intake->job,
                ),
                'name' => $this->intake->fullname,
            ]);

        $contact = $this->intake->contact;
        if ($contact === null) {
            $logger->error(sprintf('phone formatting failed for intake "%s"', $this->intake->uuid));
            throw new IntakeException('Contact details missing for intake');
        }

        try {
            $phoneNumber = (new PhoneNumber($contact->phone, 'NL'))->formatE164();
        } catch (Throwable $exception) {
            $logger->error(sprintf('phone formatting failed for intake "%s"', $this->intake->uuid));
            throw IntakeException::fromThrowable($exception);
        }

        $email = $contact->email;
        $this->withSymfonyMessage(static function (Email $message) use ($email, $phoneNumber): void {
            $headers = $message->getHeaders();
            $headers->addTextHeader('zivver-access-right', sprintf('%s sms %s', $email, $phoneNumber));
            $headers->addTextHeader('zivver-message-expiration', 'delete P30D');
        });

        return $this;
    }

    private function getAdvices(
        ?UnderlyingSuffering $intakeUnderlyingSuffering,
        ?Housemates $intakeHousemates,
        ?Symptoms $intakeSymptoms,
    ): Collection {
        $advices = new Collection();
        $advices->push($this->buildAdvice('staff'));
        $advices->push($this->buildAdvice('stayHome'));

        if (
            $intakeUnderlyingSuffering !== null
            && $intakeUnderlyingSuffering->hasUnderlyingSuffering === YesNoUnknown::yes()
            && count($intakeUnderlyingSuffering->items) > 0
        ) {
            $advices->push($this->buildAdvice('maybeImmunoComporomised'));
        }

        if ($intakeHousemates !== null && $intakeHousemates->hasHouseMates === YesNoUnknown::no()) {
            $advices->push($this->buildAdvice('hasHouseMates'));
        }

        if ($intakeSymptoms !== null && $intakeSymptoms->hasSymptoms === YesNoUnknown::yes()) {
            $advices->push($this->buildAdvice('hasSymptoms'));
        } else {
            $advices->push($this->buildAdvice('hasNoSymptoms'));
        }

        $advices->push($this->buildAdvice('medical'));
        return $advices;
    }

    private function getAdditionalAdvices(
        bool $isSelfTest,
        ?Pregnancy $intakePregnancy,
        ?RecentBirth $intakeRecentBirth,
        ?Housemates $intakeHousemates,
        ?Job $intakeJob,
    ): Collection {
        $additionalAdvices = new Collection();

        if ($isSelfTest) {
            $additionalAdvices->push($this->buildAdditionalAdvice('isSelfTestFlow'));
        }

        $isPregnant = $intakePregnancy !== null && $intakePregnancy->isPregnant === YesNoUnknown::yes();
        $hasRecentlyGivenBirth = $intakeRecentBirth !== null && $intakeRecentBirth->hasRecentlyGivenBirth === YesNoUnknown::yes();
        if ($isPregnant || $hasRecentlyGivenBirth) {
            $additionalAdvices->push($this->buildAdditionalAdvice('isPregnantOrRecentBirth'));
        }

        $hasHouseMates = $intakeHousemates !== null && $intakeHousemates->hasHouseMates === YesNoUnknown::yes();
        if ($hasHouseMates) {
            $additionalAdvices->push($this->buildAdditionalAdvice('probablyHasHouseMates'));
        }

        if ($intakeJob !== null && $intakeJob->sectors !== null) {
            foreach ($intakeJob->sectors as $jobSector) {
                if ($jobSector->group === JobSectorGroup::care()) {
                    $additionalAdvices->push($this->buildAdditionalAdvice('isJobSectorCare'));
                    break;
                }
            }
        }

        return $additionalAdvices;
    }

    private function buildAdvice(string $key): Collection
    {
        if ($this->translator === null) {
            throw new LogicException('translator should not be null');
        }
        $translationPrefix = 'intake.confirmation.advices';
        $replacements = [];

        $contagiousPeriod = $this->getContagiousPeriod();

        $replacements['contagiousPeriodStartDate'] = $this->formatDate($contagiousPeriod->getStartDate());
        Assert::notNull($contagiousPeriod->getEndDate());
        $replacements['contagiousPeriodEndDate'] = $this->formatDate($contagiousPeriod->getEndDate());

        $advice = new Collection();
        $advice->put('title', $this->translator->get(sprintf('%s.%s.title', $translationPrefix, $key)));
        $advice->put(
            'content',
            $this->translator->get(sprintf('%s.%s.content', $translationPrefix, $key), $replacements),
        );

        $linkKey = sprintf('%s.%s.link', $translationPrefix, $key);
        $linkTranslation = $this->translator->get($linkKey);
        if ($linkTranslation !== $linkKey) {
            $adviceLink = new Collection();
            $adviceLink->put('text', $this->translator->get(sprintf('%s.text', $linkKey)));
            $adviceLink->put('href', $this->translator->get(sprintf('%s.href', $linkKey)));
            $advice->put('link', $adviceLink);
        }

        return $advice;
    }

    private function buildAdditionalAdvice(string $key): Collection
    {
        if ($this->translator === null) {
            throw new LogicException('translator should not be null');
        }
        $translationPrefix = 'intake.confirmation.additionalAdvices.items';

        $additionalAdvices = new Collection();
        $additionalAdvices->put('text', $this->translator->get(sprintf('%s.%s.text', $translationPrefix, $key)));
        $additionalAdvices->put('href', $this->translator->get(sprintf('%s.%s.href', $translationPrefix, $key)));

        return $additionalAdvices;
    }

    private function formatDate(CarbonInterface $dateTime): string
    {
        return $dateTime->translatedFormat('l j F');
    }

    public function getContagiousPeriod(): CarbonPeriod
    {
        $policyFacts = IndexPolicyFacts::create(
            $this->intake->symptoms?->hasSymptoms,
            null, // medication.isImmunoCompromised, not in intake?
            null, // hospital.isAdmitted, not in intake?
            null, // hospital.reason, not in intake?
        );

        if ($this->intake->test?->dateOfSymptomOnset) {
            $policyFacts = $policyFacts->withDateOfSymptomOnset($this->intake->test->dateOfSymptomOnset);
        }

        if ($this->intake->test?->dateOfTest) {
            $policyFacts = $policyFacts->withDateOfTest($this->intake->test->dateOfTest);
        }

        $policyGuidelineHandler = $this->policyGuidelineProvider->getByPolicyVersionApplicableByFacts($policyFacts);

        return $policyGuidelineHandler->calculateContagiousPeriod($policyFacts);
    }
}
