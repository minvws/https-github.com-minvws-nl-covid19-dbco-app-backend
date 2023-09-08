<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Context\Contact as ContextContact;
use App\Models\CovidCase\Abroad;
use App\Models\CovidCase\AlternateContact;
use App\Models\CovidCase\AlternateResidency;
use App\Models\CovidCase\AlternativeLanguage;
use App\Models\CovidCase\Communication;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Contacts;
use App\Models\CovidCase\Deceased;
use App\Models\CovidCase\EduDaycare;
use App\Models\CovidCase\General;
use App\Models\CovidCase\GeneralPractitioner;
use App\Models\CovidCase\GroupTransport;
use App\Models\CovidCase\Hospital;
use App\Models\CovidCase\Housemates;
use App\Models\CovidCase\Immunity;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\Job;
use App\Models\CovidCase\Medication;
use App\Models\CovidCase\Medicine;
use App\Models\CovidCase\Pregnancy;
use App\Models\CovidCase\PrincipalContextualSettings;
use App\Models\CovidCase\RecentBirth;
use App\Models\CovidCase\RiskLocation;
use App\Models\CovidCase\SourceEnvironments;
use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\CovidCase\UnderlyingSuffering;
use App\Models\CovidCase\Vaccination;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use App\Models\Shared\Address;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\Contacts\ContactsV1;
use App\Models\Versions\CovidCase\General\GeneralV1;
use App\Models\Versions\CovidCase\General\GeneralV1UpTo1;
use App\Models\Versions\CovidCase\Immunity\ImmunityV1UpTo1;
use App\Models\Versions\CovidCase\Index\IndexV1;
use App\Models\Versions\CovidCase\Pregnancy\PregnancyV1UpTo1;
use App\Models\Versions\CovidCase\RecentBirth\RecentBirthV1UpTo1;
use App\Models\Versions\CovidCase\SourceEnvironments\SourceEnvironmentsV1UpTo1;
use App\Models\Versions\CovidCase\Test\TestV1UpTo1;
use App\Models\Versions\CovidCase\Test\TestV1UpTo3;
use App\Models\Versions\CovidCase\Test\TestV2Up;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV1UpTo1;
use App\Models\Versions\Shared\Address\AddressV1;
use App\Schema\Fields\Field;
use App\Schema\Types\ArrayType;
use App\Schema\Types\EnumVersionType;
use App\Schema\Types\SchemaType;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\CauseOfDeath;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\IsolationAdvice;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use MinVWS\DBCO\Enum\Models\Language;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\TestReason;
use MinVWS\DBCO\Enum\Models\TransportationType;
use MinVWS\DBCO\Enum\Models\VaccinationGroup;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

trait CaseFragmentGenerator
{
    protected function updateCaseWithAllFragments(EloquentCase $case): void
    {
        $case->abroad = $this->abroadFragment($case);
        $case->alternateContact = $this->alternateContactFragment($case);
        $case->alternateResidency = $this->alternateResidencyFragment($case);
        $case->alternativeLanguage = $this->alternativeLanguageFragment($case);
        $case->communication = $this->communicationFragment($case);
        $case->contact = $this->contactFragment($case);
        $case->contacts = $this->contactsFragment($case);
        $case->deceased = $this->deceasedFragment($case);
        $case->eduDaycare = $this->eduDaycareFragment($case);
        $case->general = $this->generalFragment($case);
        $case->general_practitioner = $this->generalPractitionerFragment($case);
        $case->group_transport = $this->groupTransportFragment($case);
        $case->hospital = $this->hospitalFragment($case);
        $case->housemates = $this->housematesFragment($case);
        $case->index = $this->indexFragment($case);
        $case->job = $this->jobFragment($case);
        $case->medication = $this->medicationFragment($case);
        $case->pregnancy = $this->pregnancyFragment($case);
        $case->principalContextualSettings = $this->principalContextualSettingsFragment($case);
        $case->recentBirth = $this->recentBirthFragment($case);
        $case->riskLocation = $this->riskLocationFragment($case);
        $case->sourceEnvironments = $this->sourceEnvironmentsFragment($case);
        $case->symptoms = $this->symptomsFragment($case);
        $case->test = $this->testFragment($case);
        $case->underlying_suffering = $this->underlyingSufferingFragment($case);
        $case->vaccination = $this->vaccinationFragment($case);
        $case->immunity = $this->immunityFragment($case);
        $this->createExpertQuestionWithAnswer($case);

        $case->save();
    }

    protected function abroadFragment(EloquentCase $case): Abroad
    {
        /** @var Field<SchemaType> $abroadField */
        $abroadField = $case->getSchemaVersion()->getField('abroad');

        /** @var Abroad $abroad */
        $abroad = $abroadField->newInstance();
        $abroad->wasAbroad = YesNoUnknown::yes();

        $tripsField = $abroadField->getType()->getSchemaVersion()->getField('trips');
        $trip = $tripsField->newInstance();
        $trip->departureDate = CarbonImmutable::now()->subDays(14);
        $trip->returnDate = CarbonImmutable::now();
        $trip->countries = [Country::nld(), Country::fji()];
        $trip->transportation = [TransportationType::plane(), TransportationType::bus()];
        $abroad->trips = [$trip];

        return $abroad;
    }

    protected function alternateContactFragment(EloquentCase $case): AlternateContact
    {
        /** @var Field<SchemaType> $alternateContactField */
        $alternateContactField = $case->getSchemaVersion()->getField('alternateContact');

        /** @var AlternateContact $alternateContact */
        $alternateContact = $alternateContactField->getType()->getSchemaVersion()->newInstance();
        $alternateContact->hasAlternateContact = YesNoUnknown::yes();
        $alternateContact->firstname = 'Jan';
        $alternateContact->lastname = 'Jansen';
        $alternateContact->gender = Gender::male();
        $alternateContact->relationship = Relationship::colleague();
        $alternateContact->phone = '061245678';
        $alternateContact->email = 'jan@jansen.com';
        $alternateContact->isDefaultContact = true;

        return $alternateContact;
    }

    protected function alternateResidencyFragment(EloquentCase $case): AlternateResidency
    {
        /** @var Field<SchemaType> $alternateResidencyField */
        $alternateResidencyField = $case->getSchemaVersion()->getField('alternateResidency');

        /** @var AlternateResidency $alternateResidency */
        $alternateResidency = $alternateResidencyField->getType()->getSchemaVersion()->newInstance();
        $alternateResidency->hasAlternateResidency = YesNoUnknown::yes();
        $alternateResidency->address = $alternateResidency->getSchemaVersion()->getField('address')->newInstance();
        $alternateResidency->address->postalCode = '1234AA';
        $alternateResidency->address->houseNumber = '103';
        $alternateResidency->address->houseNumberSuffix = 'a';
        $alternateResidency->address->street = 'Sesamstraat';
        $alternateResidency->address->town = 'Duckstad';
        $alternateResidency->remark = 'Remarks';

        return $alternateResidency;
    }

    protected function alternativeLanguageFragment(EloquentCase $case): AlternativeLanguage
    {
        /** @var Field<SchemaType> $alternativeLanguageField */
        $alternativeLanguageField = $case->getSchemaVersion()->getField('alternativeLanguage');

        /** @var AlternativeLanguage $alternativeLanguage */
        $alternativeLanguage = $alternativeLanguageField->getType()->getSchemaVersion()->newInstance();
        $alternativeLanguage->useAlternativeLanguage = YesNoUnknown::yes();
        $alternativeLanguage->phoneLanguages = [Language::nld(), Language::spa()];
        $alternativeLanguage->emailLanguage = EmailLanguage::en();

        return $alternativeLanguage;
    }

    protected function communicationFragment(EloquentCase $case): Communication
    {
        /** @var Field<SchemaType> $communicationField */
        $communicationField = $case->getSchemaVersion()->getField('communication');

        /** @var Communication $communication */
        $communication = $communicationField->getType()->getSchemaVersion()->newInstance();
        $communication->isolationAdviceGiven = [IsolationAdvice::liveSeperatedExplained()];
        $communication->conditionalAdviceGiven = 'Lorem Ipsum';
        $communication->otherAdviceGiven = 'Lorem Ipsum';
        $communication->particularities = 'Lorem Ipsum';

        return $communication;
    }

    protected function contactFragment(EloquentCase $case): Contact
    {
        /** @var Field<SchemaType> $contactField */
        $contactField = $case->getSchemaVersion()->getField('contact');

        /** @var Contact $contact */
        $contact = $contactField->getType()->getSchemaVersion()->newInstance();

        $contact->phone = '0612345678';
        $contact->email = 'jan@jansen.com';

        return $contact;
    }

    protected function contactsFragment(EloquentCase $case): Contacts
    {
        /** @var Field<SchemaType> $contactsField */
        $contactsField = $case->getSchemaVersion()->getField('contacts');

        /** @var Contacts $contacts */
        $contacts = $contactsField->getType()->getSchemaVersion()->newInstance();
        $contacts->shareNameWithContacts = 'yes';
        if ($contacts instanceof ContactsV1) {
            $contacts->estimatedCategory3Contacts = 8;
        }

        return $contacts;
    }

    protected function deceasedFragment(EloquentCase $case): Deceased
    {
        /** @var Field<SchemaType> $deceasedField */
        $deceasedField = $case->getSchemaVersion()->getField('deceased');

        /** @var Deceased $deceased */
        $deceased = $deceasedField->getType()->getSchemaVersion()->newInstance();
        $deceased->isDeceased = YesNoUnknown::yes();
        $deceased->deceasedAt = CarbonImmutable::now()->subDays(14);
        $deceased->cause = CauseOfDeath::covid19();

        return $deceased;
    }

    protected function eduDaycareFragment(EloquentCase $case): EduDaycare
    {
        /** @var Field<SchemaType> $eduDaycareField */
        $eduDaycareField = $case->getSchemaVersion()->getField('eduDaycare');

        /** @var EduDaycare $eduDaycare */
        $eduDaycare = $eduDaycareField->getType()->getSchemaVersion()->newInstance();

        return $eduDaycare;
    }

    protected function generalFragment(EloquentCase $case): General
    {
        /** @var Field<SchemaType> $generalField */
        $generalField = $case->getSchemaVersion()->getField('general');

        /** @var GeneralV1 $general */
        $general = $generalField->getType()->getSchemaVersion()->newInstance();
        $general->source = 'CoronIT';
        $general->notes = 'Lorem Ipsum';

        if ($general instanceof GeneralV1UpTo1) {
            $general->askedAboutCoronaMelder = true;
        }

        return $general;
    }

    protected function generalPractitionerFragment(EloquentCase $case): GeneralPractitioner
    {
        /** @var Field<SchemaType> $generalPractitionerField */
        $generalPractitionerField = $case->getSchemaVersion()->getField('generalPractitioner');

        /** @var Field<SchemaType> $addressField */
        $addressField = $generalPractitionerField->getType()->getSchemaVersion()->getField('address');

        /** @var Address $address */
        $address = $addressField->getType()->getSchemaVersion()->newInstance();
        $address->postalCode = '1234AA';
        $address->houseNumber = '103';
        $address->houseNumberSuffix = 'a';
        $address->street = 'Sesamstraat';
        $address->town = 'Duckstad';

        /** @var GeneralPractitioner $generalPractitioner */
        $generalPractitioner = $generalPractitionerField->getType()->getSchemaVersion()->newInstance();
        $generalPractitioner->name = 'Doc';
        $generalPractitioner->practiceName = 'Docs';
        $generalPractitioner->address = $address;
        $generalPractitioner->hasInfectionNotificationConsent = true;

        return $generalPractitioner;
    }

    protected function groupTransportFragment(EloquentCase $case): GroupTransport
    {
        /** @var Field<SchemaType> $groupTransportField */
        $groupTransportField = $case->getSchemaVersion()->getField('groupTransport');

        /** @var GroupTransport $groupTransport */
        $groupTransport = $groupTransportField->getType()->getSchemaVersion()->newInstance();
        $groupTransport->withReservedSeats = YesNoUnknown::yes();

        return $groupTransport;
    }

    protected function hospitalFragment(EloquentCase $case): Hospital
    {
        /** @var Field<SchemaType> $hospitalField */
        $hospitalField = $case->getSchemaVersion()->getField('hospital');

        /** @var Hospital $hospital */
        $hospital = $hospitalField->getType()->getSchemaVersion()->newInstance();
        $hospital->isAdmitted = YesNoUnknown::yes();
        $hospital->name = 'Ziekenhuis';
        $hospital->location = 'Ziekenhuis';
        $hospital->admittedAt = CarbonImmutable::now()->subDays(10);
        $hospital->releasedAt = CarbonImmutable::now()->subDays(3);
        $hospital->reason = HospitalReason::covid();
        $hospital->hasGivenPermission = YesNoUnknown::yes();
        $hospital->practitioner = 'Doc';
        $hospital->practitionerPhone = '0612345678';
        $hospital->isInICU = YesNoUnknown::yes();
        $hospital->admittedInICUAt = CarbonImmutable::now()->subDays(3);

        return $hospital;
    }

    protected function housematesFragment(EloquentCase $case): Housemates
    {
        /** @var Field<SchemaType> $housematesField */
        $housematesField = $case->getSchemaVersion()->getField('housemates');

        /** @var Housemates $housemates */
        $housemates = $housematesField->getType()->getSchemaVersion()->newInstance();
        $housemates->hasHouseMates = YesNoUnknown::yes();
        $housemates->hasOwnFacilities = true;
        $housemates->hasOwnKitchen = true;
        $housemates->hasOwnBedroom = true;
        $housemates->hasOwnRestroom = true;
        $housemates->canStrictlyIsolate = true;
        $housemates->bottlenecks = 'Lorem Ipsum';

        return $housemates;
    }

    protected function indexFragment(EloquentCase $case): Index
    {
        /** @var Field<SchemaType> $indexField */
        $indexField = $case->getSchemaVersion()->getField('index');

        /** @var IndexV1 $index */
        $index = $indexField->getType()->getSchemaVersion()->newInstance();
        $index->firstname = 'Jessica';
        $index->lastname = 'Jansen';
        $index->dateOfBirth = new DateTimeImmutable('1994-11-05');
        $index->gender = Gender::female();

        /** @var Field<SchemaType> $addressField */
        $addressField = $index->getSchemaVersion()->getField('address');

        /** @var AddressV1 $address */
        $address = $addressField->getType()->getSchemaVersion()->newInstance();
        $address->postalCode = '1234AA';
        $address->houseNumber = '103';
        $address->houseNumberSuffix = 'a';
        $address->street = 'Sesamstraat';
        $address->town = 'Duckstad';

        $index->address = $address;

        return $index;
    }

    protected function jobFragment(EloquentCase $case): Job
    {
        /** @var Field<SchemaType> $jobField */
        $jobField = $case->getSchemaVersion()->getField('job');

        /** @var Job $job */
        $job = $jobField->getType()->getSchemaVersion()->newInstance();
        $job->wasAtJob = YesNoUnknown::yes();
        $job->sectors = [JobSector::mantelzorg(), JobSector::middelbaarOnderwijsOfMiddelbaarBeroepsonderwijs()];
        $job->professionCare = ProfessionCare::logopedist();
        $job->closeContactAtJob = YesNoUnknown::yes();
        $job->professionOther = ProfessionOther::kapper();
        $job->otherProfession = 'Lorem Ipsum';

        return $job;
    }

    protected function medicationFragment(EloquentCase $case): Medication
    {
        /** @var Field<SchemaType> $medicationField */
        $medicationField = $case->getSchemaVersion()->getField('medication');

        /** @var Medication $medication */
        $medication = $medicationField->getType()->getSchemaVersion()->newInstance();

        /** @var Medicine $medicine */
        $medicine = Medicine::getSchema()->getCurrentVersion()->newInstance();
        $medicine->name = 'Lorem';
        $medicine->remark = 'Lorem Ipsum';
        $medicine->knownEffects = 'dolor sit amet';

        $medication->hasMedication = YesNoUnknown::yes();
        $medication->medicines = [$medicine];
        $medication->isImmunoCompromised = YesNoUnknown::yes();
        $medication->immunoCompromisedRemarks = 'Lorem Ipsum';
        $medication->hasGivenPermission = YesNoUnknown::yes();
        $medication->practitioner = 'Doc';
        $medication->practitionerPhone = '0612345678';
        $medication->hospitalName = 'Ziekenhuis';

        return $medication;
    }

    protected function pregnancyFragment(EloquentCase $case): Pregnancy
    {
        /** @var Field<SchemaType> $pregnancyField */
        $pregnancyField = $case->getSchemaVersion()->getField('pregnancy');

        /** @var Pregnancy $pregnancy */
        $pregnancy = $pregnancyField->getType()->getSchemaVersion()->newInstance();
        $pregnancy->isPregnant = YesNoUnknown::yes();

        if ($pregnancy instanceof PregnancyV1UpTo1) {
            $pregnancy->dueDate = CarbonImmutable::now()->addWeeks(3);
        }

        return $pregnancy;
    }

    protected function principalContextualSettingsFragment(EloquentCase $case): PrincipalContextualSettings
    {
        /** @var Field<SchemaType> $principalContextualSettingField */
        $principalContextualSettingField = $case->getSchemaVersion()->getField('principalContextualSettings');
        /** @var PrincipalContextualSettings $principalContextualSettings */
        $principalContextualSettings = $principalContextualSettingField->getType()->getSchemaVersion()->newInstance();

        $principalContextualSettings->hasPrincipalContextualSettings = true;
        $principalContextualSettings->items = ['Lorem', 'Ipsum'];
        $principalContextualSettings->otherItems = ['dolor sit amet'];

        return $principalContextualSettings;
    }

    protected function recentBirthFragment(EloquentCase $case): RecentBirth
    {
        /** @var RecentBirth $recentBirth */
        $recentBirth = $case->getSchemaVersion()->getField('recentBirth')->newInstance();

        if ($recentBirth instanceof RecentBirthV1UpTo1) {
            $recentBirth->hasRecentlyGivenBirth = YesNoUnknown::yes();
            $recentBirth->birthDate = CarbonImmutable::now()->subWeeks(2);
            $recentBirth->birthRemarks = 'Lorem Ipsum';
        }

        return $recentBirth;
    }

    protected function riskLocationFragment(EloquentCase $case): RiskLocation
    {
        /** @var RiskLocation $riskLocation */
        $riskLocation = $case->getSchemaVersion()->getField('riskLocation')->newInstance();

        $riskLocation->isLivingAtRiskLocation = YesNoUnknown::yes();
        $riskLocation->type = RiskLocationType::prison();
        $riskLocation->otherType = 'Lorem Ipsum';

        return $riskLocation;
    }

    protected function sourceEnvironmentsFragment(EloquentCase $case): SourceEnvironments
    {
        /** @var SourceEnvironments $sourceEnvironments */
        $sourceEnvironments = $case->getSchemaVersion()->getField('sourceEnvironments')->newInstance();

        if ($sourceEnvironments instanceof SourceEnvironmentsV1UpTo1) {
            $sourceEnvironments->hasLikelySourceEnvironments = YesNoUnknown::yes();
            $sourceEnvironments->likelySourceEnvironments = ['Lorem', 'Ipsum'];
        }

        return $sourceEnvironments;
    }

    protected function symptomsFragment(EloquentCase $case): Symptoms
    {
        /** @var Symptoms $symptoms */
        $symptoms = $case->getSchemaVersion()->getField('symptoms')->newInstance();

        $symptoms->hasSymptoms = YesNoUnknown::yes();
        $symptoms->symptoms = [Symptom::dizziness(), Symptom::headache()];
        $symptoms->otherSymptoms = ['Lorem Ipsum', 'dolor sit amet'];
        $symptoms->diseaseCourse = 'Lorem Ipsum';

        if ($symptoms instanceof SymptomsV1UpTo1) {
            $symptoms->wasSymptomaticAtTimeOfCall = YesNoUnknown::yes();
        }

        return $symptoms;
    }

    protected function testFragment(EloquentCase $case): Test
    {
        /** @var Field<SchemaType> $testField */
        $testField = $case->getSchemaVersion()->getField('test');

        /** @var TestV1UpTo1 | TestV2Up $testFragment */
        $testFragment = $testField->getType()->getSchemaVersion()->newInstance();
        $testFragment->dateOfSymptomOnset = CarbonImmutable::now()->subWeek();
        $testFragment->isSymptomOnsetEstimated = true;
        $testFragment->dateOfTest = CarbonImmutable::now()->subDays(2);
        $testFragment->dateOfResult = CarbonImmutable::now()->subDay();
        $testFragment->dateOfInfectiousnessStart = CarbonImmutable::now()->subDays(4);
        $testFragment->reasons = [TestReason::work(), TestReason::outbreak()];
        $testFragment->otherReason = 'Lorem Ipsum';
        $testFragment->infectionIndicator = InfectionIndicator::selfTest();
        $testFragment->selfTestIndicator = SelfTestIndicator::antigen();
        $testFragment->labTestIndicator = LabTestIndicator::molecular();
        $testFragment->otherLabTestIndicator = 'Lorem Ipsum';
        $testFragment->monsterNumber = 'MM-1234';
        $testFragment->isReinfection = YesNoUnknown::yes();
        $testFragment->previousInfectionDateOfSymptom = CarbonImmutable::now()->subMonths(4);
        $testFragment->previousInfectionSymptomFree = true;
        $testFragment->previousInfectionProven = YesNoUnknown::yes();
        $testFragment->contactOfConfirmedInfection = true;
        $testFragment->previousInfectionReported = YesNoUnknown::yes();

        if ($testFragment instanceof TestV1UpTo3) {
            $testFragment->previousInfectionHpzoneNumber = '9991234';
        } else {
            $testFragment->previousInfectionCaseNumber = '9991234';
        }

        return $testFragment;
    }

    protected function underlyingSufferingFragment(EloquentCase $case): UnderlyingSuffering
    {
        /** @var UnderlyingSuffering $underlyingSuffering */
        $underlyingSuffering = $case->getSchemaVersion()->getField('underlyingSuffering')->newInstance();
        $underlyingSufferingEnum = $underlyingSuffering
            ->getSchemaVersion()
            ->getExpectedField('items')
            ->getExpectedType(ArrayType::class)
            ->getExpectedElementType(EnumVersionType::class)
            ->getEnumVersion();

        $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
        $underlyingSuffering->hasUnderlyingSuffering = YesNoUnknown::yes();
        // 'parkinson' is chosen because this is an unversioned Enum value and always available.
        $underlyingSuffering->items = [$underlyingSufferingEnum->from('parkinson')];
        $underlyingSuffering->otherItems = ['Lorem Ipsum', 'dolor sit amet'];
        $underlyingSuffering->remarks = 'Lorem Ipsum';

        return $underlyingSuffering;
    }

    protected function vaccinationFragment(EloquentCase $case): Vaccination
    {
        /** @var Field<SchemaType> $vaccinationField */
        $vaccinationField = $case->getSchemaVersion()->getField('vaccination');

        /** @var Vaccination $vaccination */
        $vaccination = $vaccinationField->getType()->getSchemaVersion()->newInstance();

        if ($vaccination instanceof VaccinationV1UpTo1) {
            $vaccination->hasReceivedInvite = YesNoUnknown::yes();
            $vaccination->groups = [VaccinationGroup::ageAbove60()];
            $vaccination->otherGroup = 'Lorem Ipsum';
        }

        $vaccination->isVaccinated = YesNoUnknown::yes();

        /** @var Field<SchemaType> $vaccineInjectionsField */
        $vaccineInjectionsField = $vaccination->getSchemaVersion()->getField('vaccineInjections');

        /** @var VaccineInjection $vaccineInjectionFirst */
        $vaccineInjectionFirst = $vaccineInjectionsField->newInstance();
        $vaccineInjectionFirst->injectionDate = CarbonImmutable::now()->subWeeks(12);
        $vaccineInjectionFirst->vaccineType = Vaccine::janssen();

        /** @var VaccineInjection $vaccineInjectionOther */
        $vaccineInjectionOther = $vaccineInjectionsField->newInstance();
        $vaccineInjectionOther->injectionDate = CarbonImmutable::now()->subWeeks(5);
        $vaccineInjectionOther->vaccineType = Vaccine::other();
        $vaccineInjectionOther->otherVaccineType = 'custom type';
        $vaccineInjectionOther->isInjectionDateEstimated = true;

        /** @var VaccineInjection $vaccineInjectionMinimal */
        $vaccineInjectionMinimal = $vaccineInjectionsField->newInstance();
        $vaccineInjectionMinimal->injectionDate = CarbonImmutable::now()->subWeeks(3);

        $vaccination->vaccineInjections = [
            $vaccineInjectionFirst,
            $vaccineInjectionOther,
            $vaccineInjectionMinimal,
        ];

        return $vaccination;
    }

    protected function createContextFragment(EloquentCase $case): Context
    {
        $contact = ContextContact::newInstanceWithVersion(Contact::getSchema()->getCurrentVersion()->getVersion());
        $contact->firstname = 'Eddy';
        $contact->lastname = 'Roosevelt';
        $contact->phone = '0612312312';
        $contact->notificationNamedConsent = true;
        $contact->notificationConsent = YesNoUnknown::yes();

        return $this->createContextForCase($case, [
            'remarks' => 'Corporis tempora ullam fuga quae aut minima pariatur.',
            'explanation' => '',
            'detailed_explanation' => 'Blanditiis facere dolorem',
            'is_source' => false,
            'relationship' => false,
            'place_uuid' => static function () {
                return Place::factory()->create([
                    'label' => 'Testlocatie',
                    'street' => 'Straatweg',
                    'housenumber' => '1a',
                    'postalcode' => '1234AA',
                    'town' => 'Testdorp',
                    'country' => 'NL',
                ]);
            },
            'contact' => $contact,
        ]);
    }

    public function immunityFragment(EloquentCase $case): Immunity
    {
        /** @var Field<SchemaType> $immunityField */
        $immunityField = $case->getSchemaVersion()->getField('immunity');

        /** @var Immunity $immunity */
        $immunity = $immunityField->getType()->getSchemaVersion()->newInstance();

        if ($immunity instanceof ImmunityV1UpTo1) {
            $immunity->isImmune = YesNoUnknown::yes();
            $immunity->remarks = 'Lorum ipsum';
        }

        return $immunity;
    }

    public function createExpertQuestionWithAnswer(EloquentCase $case): void
    {
        $this->createExpertQuestionWithAnswerForCase(
            $case,
            [
                'user_uuid' => $this->createUser(['name' => 'Henk Testmeneer']),
                'type' => ExpertQuestionType::medicalSupervision(),
                'created_at' => CarbonImmutable::create(2022, 4, 28, 14, 20, 00),
                'subject' => 'Ex·molestias·quibusdam·eius·culpa·quibusdam·quo.',
                'question' => 'In·possimus·necessitatibus·veritatis.',
            ],
            [
                'answered_by' => $this->createUser(['name' => 'Test Henkmeneer'], 'medical_supervisor'),
                'created_at' => CarbonImmutable::create(2022, 4, 28, 14, 20, 00),
                'answer' => 'Deleniti·nesciunt·cumque·sunt·exercitationem.',
            ],
        );
    }
}
