<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Test\TestV4;
use DateTime;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use MinVWS\DBCO\Enum\Models\TestReason;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case')]
#[Group('covidcase')]
#[Group('fragment')]
class EncodeTest extends FeatureTestCase
{
    public function testEncodeDecode(): void
    {
        // NOTE:
        // We explicitly don't save the case in the database, we just use it to make
        // the dependency on case->symptoms->hasSymptoms available.
        $case = EloquentCase::getSchema()->getCurrentVersion()->newInstance();
        $case->created_at = new DateTime();
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();

        /** @var TestV4 $testFragment */
        $testFragment = $case->test;
        $testFragment->dateOfInfectiousnessStart = DateTime::createFromFormat('!Y-m-d', '2021-02-01');
        $testFragment->dateOfSymptomOnset = DateTime::createFromFormat('!Y-m-d', '2021-02-02');
        $testFragment->dateOfTest = DateTime::createFromFormat('!Y-m-d', '2021-02-03');
        $testFragment->dateOfResult = DateTime::createFromFormat('!Y-m-d', '2021-02-04');
        $testFragment->infectionIndicator = InfectionIndicator::selfTest();
        $testFragment->selfTestIndicator = SelfTestIndicator::molecular();
        $testFragment->labTestIndicator = LabTestIndicator::molecular();
        $testFragment->isReinfection = YesNoUnknown::yes();
        $testFragment->isSymptomOnsetEstimated = false;
        $testFragment->monsterNumber = "123456";
        $testFragment->otherReason = "some other reason";
        $testFragment->previousInfectionDateOfSymptom = DateTime::createFromFormat('!Y-m-d', '2020-11-01');
        $testFragment->previousInfectionCaseNumber = "HPZone123";
        $testFragment->previousInfectionSymptomFree = true;
        $testFragment->reasons = [TestReason::contact(), TestReason::outbreak()];
        $testFragment->previousInfectionProven = YesNoUnknown::yes();
        $testFragment->contactOfConfirmedInfection = true;
        $testFragment->previousInfectionReported = YesNoUnknown::yes();
        $testFragment->otherLabTestIndicator = "abc";
        $testFragment->selfTestLabTestDate = DateTime::createFromFormat('!Y-m-d', '2020-11-01');
        $testFragment->selfTestLabTestResult = TestResult::positive();

        $before = $testFragment->getData();
        $encoded = (new Encoder())->encode($testFragment);
        $case->test = $case->getSchemaVersion()->getExpectedField('test')->newInstance();
        $decoded = (new Decoder())->decode($encoded)->decodeObject(Test::class, $case->test);
        $after = $decoded->getData();

        $this->assertEquals($before, $after);
    }
}
