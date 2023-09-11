<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Client\Soap;

use App\Http\Client\Soap\NativeSoapClientFactory;
use SoapClient;
use Tests\Unit\UnitTestCase;

final class NativeSoapClientFactoryTest extends UnitTestCase
{
    public function testCreate(): void
    {
        $uri = $this->faker->url();
        $location = $this->faker->url();

        $options = ['uri' => $uri, 'location' => $location];

        $nativeSoapClientFactory = new NativeSoapClientFactory();
        $nativeSoapClient = $nativeSoapClientFactory->create(null, $options);

        $this->assertInstanceOf(SoapClient::class, $nativeSoapClient);
    }
}
