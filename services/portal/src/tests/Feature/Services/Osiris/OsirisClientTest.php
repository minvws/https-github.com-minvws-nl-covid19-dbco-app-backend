<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use Ackintosh\Ganesha;
use App\Dto\Osiris\Client\Credentials;
use App\Dto\Osiris\Client\SoapMessage;
use App\Exceptions\Osiris\Client\ClientException;
use App\Exceptions\Osiris\Client\ClientExceptionInterface;
use App\Helpers\Config;
use App\Models\Metric\CircuitBreaker\Availability;
use App\Repositories\Metric\MetricRepository;
use App\Services\Osiris\OsirisClient;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SimpleXMLElement;
use Tests\Feature\FeatureTestCase;

use function config;
use function sprintf;

#[Group('osiris')]
final class OsirisClientTest extends FeatureTestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testCircuitBreaker(): void
    {
        $soapMessage = new SoapMessage(
            new Credentials('sysLogin', 'sysPassword', 'userLogin'),
            new SimpleXMLElement('<foobar/>'),
            $this->faker->bothify('?#?#?#?#?#?#'),
        );

        $service = Config::string('services.osiris.service_name');

        config()->set('services.osiris.rate_strategy.time_window', 100);
        config()->set('services.osiris.rate_strategy.failure_rate_threshold', 1);
        config()->set('services.osiris.rate_strategy.minimum_requests', 1);
        config()->set('services.osiris.rate_strategy.interval_to_half_open', 5000);

        $this->mock(MetricRepository::class, static function (MockInterface $mock) use ($service): void {
            $mock->expects('measureGauge')
                ->with(Mockery::on(static function (Availability $availability) use ($service): bool {
                    $name = sprintf('%s_circuit_breaker_gauge', $service);

                    $hasValidName = $availability->getName() === $name;
                    $hasValidValue = $availability->getValue() === 1.0;

                    return $hasValidName && $hasValidValue;
                }));
        });

        $ganesha = $this->getGaneshaWithOsirisConfiguration();
        $ganesha->failure($service);

        $osirisClient = $this->app->get(OsirisClient::class);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('circuit breaker open');

        $osirisClient->putMessage($soapMessage);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getGaneshaWithOsirisConfiguration(): Ganesha
    {
        $ganesha = $this->app->get('osiris.ganesha');
        $this->assertInstanceOf(Ganesha::class, $ganesha);

        return $ganesha;
    }
}
