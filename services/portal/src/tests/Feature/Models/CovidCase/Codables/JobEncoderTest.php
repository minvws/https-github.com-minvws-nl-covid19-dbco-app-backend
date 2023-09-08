<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Job;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function count;

class JobEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $wasAtJob = $this->faker->randomElement(YesNoUnknown::all());
        $sectors = $this->faker->randomElements(
            JobSector::all(),
            $this->faker->numberBetween(0, count(JobSector::all())),
        );
        $professionCare = $this->faker->randomElement(ProfessionCare::all());
        $closeContactAtJob = $this->faker->randomElement(YesNoUnknown::all());
        $professionOther = $this->faker->randomElement(ProfessionOther::all());
        $otherProfession = $this->faker->optional()->sentence();
        $particularities = $this->faker->optional()->paragraph();

        $job = Job::getSchema()->getVersion(1)->getTestFactory()->make([
            'wasAtJob' => $wasAtJob,
            'sectors' => $sectors,
            'professionCare' => $professionCare,
            'closeContactAtJob' => $closeContactAtJob,
            'professionOther' => $professionOther,
            'otherProfession' => $otherProfession,
            'particularities' => $particularities,
        ]);
        $encoded = (new Encoder())->encode($job);

        $this->assertEquals($wasAtJob, $encoded->wasAtJob);
        $this->assertEquals($sectors, $encoded->sectors);
        $this->assertEquals($professionCare, $encoded->professionCare);
        $this->assertEquals($closeContactAtJob, $encoded->closeContactAtJob);
        $this->assertEquals($professionOther, $encoded->professionOther);
        $this->assertEquals($otherProfession, $encoded->otherProfession);
        $this->assertEquals($particularities, $encoded->particularities);
    }
}
