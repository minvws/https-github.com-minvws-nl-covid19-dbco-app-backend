<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use Carbon\CarbonImmutable;
use MinVWS\Codable\Decoder;
use Tests\Unit\UnitTestCase;

class DecodingContainerTest extends UnitTestCase
{
    public function testDecodeDateTime(): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $data = [
            'date' => CarbonImmutable::now()->toIso8601String(),
        ];

        $decoder = new Decoder();
        $container = $decoder->decode($data);

        $this->assertTrue(CarbonImmutable::now()->equalTo(CarbonImmutable::instance($container->date->decodeDateTime())));
    }
}
