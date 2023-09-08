<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\CovidCase\AlternateContact;
use App\Models\CovidCase\AlternateResidency;
use App\Models\CovidCase\Communication;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\General;
use App\Models\CovidCase\GeneralPractitioner;
use App\Models\CovidCase\GeneralPractitionerAddress;
use App\Models\CovidCase\Hospital;
use App\Models\CovidCase\Housemates;
use App\Models\CovidCase\Immunity;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\IndexAddress;
use App\Models\CovidCase\Job;
use App\Models\CovidCase\Medication;
use App\Models\CovidCase\Medicine;
use App\Models\CovidCase\PrincipalContextualSettings;
use App\Models\CovidCase\RecentBirth;
use App\Models\CovidCase\SourceEnvironments;
use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\CovidCase\UnderlyingSuffering;
use App\Models\CovidCase\Vaccination;
use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\Address;
use App\Models\Shared\VaccineInjection;
use App\Services\CaseFragmentService;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class CaseFragmentServiceTest extends FeatureTestCase
{
    private EloquentCase $case;
    private CaseFragmentService $caseFragmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createUser();
        $this->case = $this->createCaseForUser($user);

        $this->caseFragmentService = $this->app->get(CaseFragmentService::class);
    }

    protected function tearDown(): void
    {
        $this->assertDatabaseHas('covidcase', ['uuid' => $this->case->uuid]);

        parent::tearDown();
    }

    public function testStoreGeneralFragmentMaxLengthFoo(): void
    {
        $general = General::newInstanceWithVersion(1);
        $general->source = Str::random(250);
        $general->notes = Str::random(5000);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'general', $general);
    }

    public function testStoreIndexFragmentMaxLength(): void
    {
        $address = IndexAddress::newInstanceWithVersion(1);
        $address->postalCode = Str::random(10);
        $address->houseNumber = Str::random(10);
        $address->houseNumberSuffix = Str::random(10);
        $address->street = Str::random(250);
        $address->town = Str::random(250);

        $index = Index::newInstanceWithVersion(1);
        $index->firstname = Str::random(250);
        $index->lastname = Str::random(250);
        $index->address = $address;
        $index->bsnCensored = Str::random(9);
        $index->bsnLetters = Str::random(25);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'index', $index);
    }

    public function testStoreContactFragmentMaxLength(): void
    {
        $contact = Contact::newInstanceWithVersion(1);
        $contact->phone = Str::random(25);
        $contact->email = Str::random(250);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'contact', $contact);
    }

    public function testStoreAlternateContactFragmentMaxLength(): void
    {
        $alternateContact = AlternateContact::newInstanceWithVersion(1);
        $alternateContact->firstname = Str::random(250);
        $alternateContact->lastname = Str::random(500);
        $alternateContact->phone = Str::random(25);
        $alternateContact->email = Str::random(250);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'alternateContact', $alternateContact);
    }

    public function testStoreSymptomsFragmentMaxLength(): void
    {
        $otherSymptoms = [];
        for ($i = 0; $i < 25; $i++) {
            $otherSymptoms[] = Str::random(500);
        }

        $symptoms = Symptoms::newInstanceWithVersion(1);
        $symptoms->otherSymptoms = $otherSymptoms;
        $symptoms->diseaseCourse = Str::random(5000);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'alternateContact', $symptoms);
    }

    public function testStoreTestFragmentMaxLength(): void
    {
        $test = Test::getSchema()->getCurrentVersion()->newInstance();
        $test->otherReason = Str::random(500);
        $test->otherLabTestIndicator = Str::random(500);
        $test->monsterNumber = Str::random(500);
        $test->previousInfectionCaseNumber = Str::random(7);
        $test->monsterNumber = Str::random();

        $this->caseFragmentService->storeFragment($this->case->uuid, 'test', $test);
    }

    public function testStoreVaccinationFragmentMaxLength(): void
    {
        $vaccineInjection = VaccineInjection::newInstanceWithVersion(1);
        $vaccineInjection->otherVaccineType = Str::random(500);

        $vaccination = Vaccination::newInstanceWithVersion(1);
        $vaccination->otherGroup = Str::random(500);
        $vaccination->vaccineInjections = [$vaccineInjection];

        $this->caseFragmentService->storeFragment($this->case->uuid, 'vaccination', $vaccination);
    }

    public function testStoreHospitalFragmentMaxLength(): void
    {
        $hospital = Hospital::newInstanceWithVersion(1);
        $hospital->name = Str::random(500);
        $hospital->location = Str::random(250);
        $hospital->practitioner = Str::random(500);
        $hospital->practitionerPhone = Str::random(25);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'hospital', $hospital);
    }

    public function testStoreUnderlyingSufferingFragmentMaxLength(): void
    {
        $otherItems = [];
        for ($i = 0; $i < 25; $i++) {
            $otherItems[] = Str::random(100);
        }

        $underlyingSuffering = UnderlyingSuffering::newInstanceWithVersion(1);
        $underlyingSuffering->otherItems = $otherItems;
        $underlyingSuffering->remarks = Str::random(5000);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'underlying_suffering', $underlyingSuffering);
    }

    public function testStoreRecentBirthFragmentMaxLength(): void
    {
        $recentBirth = RecentBirth::newInstanceWithVersion(1);
        $recentBirth->birthRemarks = Str::random(5000);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'recentBirth', $recentBirth);
    }

    public function testStoreMedicationFragmentMaxLength(): void
    {
        $medicines = [];
        for ($i = 0; $i < 25; $i++) {
            $medicine = Medicine::newInstanceWithVersion(1);
            $medicine->name = Str::random(500);
            $medicine->remark = Str::random(5000);
            $medicine->knownEffects = Str::random(500);

            $medicines[] = $medicine;
        }

        $medication = Medication::newInstanceWithVersion(1);
        $medication->immunoCompromisedRemarks = Str::random(5000);
        $medication->practitioner = Str::random(500);
        $medication->practitionerPhone = Str::random(25);
        $medication->hospitalName = Str::random(300);
        $medication->medicines = $medicines;

        $this->caseFragmentService->storeFragment($this->case->uuid, 'medication', $medication);
    }

    public function testStoreGeneralPractitionerFragmentMaxLength(): void
    {
        $address = GeneralPractitionerAddress::newInstanceWithVersion(1);
        $address->postalCode = Str::random(10);
        $address->houseNumber = Str::random(10);
        $address->houseNumberSuffix = Str::random(10);
        $address->street = Str::random(250);
        $address->town = Str::random(250);

        $generalPractitioner = GeneralPractitioner::newInstanceWithVersion(1);
        $generalPractitioner->name = Str::random(250);
        $generalPractitioner->practiceName = Str::random(250);
        $generalPractitioner->address = $address;

        $this->caseFragmentService->storeFragment($this->case->uuid, 'general_practitioner', $generalPractitioner);
    }

    public function testStoreAlternateResidencyFragmentMaxLength(): void
    {
        $address = Address::newInstanceWithVersion(1);
        $address->postalCode = Str::random(10);
        $address->houseNumber = Str::random(10);
        $address->houseNumberSuffix = Str::random(10);
        $address->street = Str::random(250);
        $address->town = Str::random(250);

        $alternateResidency = AlternateResidency::newInstanceWithVersion(1);
        $alternateResidency->address = $address;
        $alternateResidency->remark = Str::random(5000);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'alternateResidency', $alternateResidency);
    }

    public function testStoreHousematesFragmentMaxLength(): void
    {
        $housemates = Housemates::newInstanceWithVersion(1);
        $housemates->bottlenecks = Str::random(5000);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'housemates', $housemates);
    }

    public function testStoreJobFragmentMaxLength(): void
    {
        $job = Job::newInstanceWithVersion(1);
        $job->otherProfession = Str::random(255);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'job', $job);
    }

    public function testStorePrincipalContextualSettingsFragmentMaxLength(): void
    {
        $items = [];
        for ($i = 0; $i < 25; $i++) {
            $items[] = Str::random(100);
        }

        $principalContextualSettings = PrincipalContextualSettings::newInstanceWithVersion(1);
        $principalContextualSettings->items = $items;
        $principalContextualSettings->otherItems = $items;

        $this->caseFragmentService->storeFragment($this->case->uuid, 'principalContextualSettings', $principalContextualSettings);
    }

    public function testStoreSourceEnvironmentsFragmentMaxLength(): void
    {
        $likelySourceEnvironments = [];
        for ($i = 0; $i < 25; $i++) {
            $likelySourceEnvironments[] = Str::random(100);
        }

        $sourceEnvironments = SourceEnvironments::newInstanceWithVersion(1);
        $sourceEnvironments->likelySourceEnvironments = $likelySourceEnvironments;

        $this->caseFragmentService->storeFragment($this->case->uuid, 'sourceEnvironments', $sourceEnvironments);
    }

    public function testStoreCommunicationFragmentMaxLength(): void
    {
        $communication = Communication::newInstanceWithVersion(1);
        $communication->conditionalAdviceGiven = Str::random(500);
        $communication->otherAdviceGiven = Str::random(500);
        $communication->particularities = Str::random(500);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'communication', $communication);
    }

    public function testStoreImmunityFragmentMaxLength(): void
    {
        $immunity = Immunity::newInstanceWithVersion(1);
        $immunity->remarks = Str::random(5000);

        $this->caseFragmentService->storeFragment($this->case->uuid, 'immunity', $immunity);
    }
}
