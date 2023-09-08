<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\ExtensiveContactTracing;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\BCOType;
use MinVWS\DBCO\Enum\Models\ExtensiveContactTracingReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class ExtensiveContactTracingTest extends FeatureTestCase
{
    public function testEncodeV3(): void
    {
        $receivesExtensiveContactTracing = $this->faker->randomElement(BCOType::all());
        $otherDescription = $this->faker->text(5000);

        $extensiveContactTracing = ExtensiveContactTracing::getSchema()->getVersion(3)->getTestFactory()->make([
            'receivesExtensiveContactTracing' => $receivesExtensiveContactTracing,
            'otherDescription' => $otherDescription,
        ]);

        $extensiveContactTracingEncoder = new Encoder();
        $encoded = $extensiveContactTracingEncoder->encode($extensiveContactTracing);

        $this->assertEquals(3, $encoded->schemaVersion);
        $this->assertEquals($receivesExtensiveContactTracing, $encoded->receivesExtensiveContactTracing);
        $this->assertEquals($otherDescription, $encoded->otherDescription);
    }

    public function testEncodeV2(): void
    {
        $receivesExtensiveContactTracing = $this->faker->randomElement(BCOType::all());
        $reasons = $this->faker->randomElements(ExtensiveContactTracingReason::all());
        $notes = $this->faker->text(5000);
        $otherDescription = $this->faker->text(5000);

        $extensiveContactTracing = ExtensiveContactTracing::getSchema()->getVersion(2)->getTestFactory()->make([
            'receivesExtensiveContactTracing' => $receivesExtensiveContactTracing,
            'reasons' => $reasons,
            'notes' => $notes,
            'otherDescription' => $otherDescription,
        ]);

        $extensiveContactTracingEncoder = new Encoder();
        $encoded = $extensiveContactTracingEncoder->encode($extensiveContactTracing);

        $this->assertEquals(2, $encoded->schemaVersion);
        $this->assertEquals($receivesExtensiveContactTracing, $encoded->receivesExtensiveContactTracing);
        $this->assertEquals($otherDescription, $encoded->otherDescription);
        $this->assertEquals($notes, $encoded->notes);
    }

    public function testEncodeV1(): void
    {
        $receivesExtensiveContactTracing = $this->faker->randomElement(YesNoUnknown::all());
        $reasons = $this->faker->randomElements(ExtensiveContactTracingReason::all());
        $notes = $this->faker->text(5000);

        $extensiveContactTracing = ExtensiveContactTracing::getSchema()->getVersion(1)->getTestFactory()->make([
            'receivesExtensiveContactTracing' => $receivesExtensiveContactTracing,
            'reasons' => $reasons,
            'notes' => $notes,
        ]);

        $extensiveContactTracingEncoder = new Encoder();
        $encoded = $extensiveContactTracingEncoder->encode($extensiveContactTracing);

        $this->assertEquals(1, $encoded->schemaVersion);
        $this->assertEquals($receivesExtensiveContactTracing, $encoded->receivesExtensiveContactTracing);
        $this->assertEquals($reasons, $encoded->reasons);
        $this->assertEquals($notes, $encoded->notes);
    }
}
