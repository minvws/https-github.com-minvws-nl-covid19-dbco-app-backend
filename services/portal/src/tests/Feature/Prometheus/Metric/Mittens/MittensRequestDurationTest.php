<?php

declare(strict_types=1);

namespace Tests\Feature\Prometheus\Metric\Mittens;

use App\Events\Mittens\MittensRequestDurationMeasured;
use App\Http\Client\Guzzle\MittensClient;
use App\Http\Client\Guzzle\MittensClientException;
use App\Http\Requests\Mittens\MittensRequest;
use App\Listeners\Mittens\MittensRequestDurationMeasuredHandler;
use App\Models\Metric\HistogramMetric;
use App\Repositories\Metric\MetricRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use JsonException;
use MinVWS\Timer\Duration;
use Tests\Feature\FeatureTestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

class MittensRequestDurationTest extends FeatureTestCase
{
    private const HTTPS_EXAMPLE_COM = 'https://example.com';

    public function getMittensClient(): mixed
    {
        $mockHandler = new MockHandler([
            new Response(202, ['Content-Type' => 'application/json'], json_encode([
                'data' => [],
            ], JSON_THROW_ON_ERROR)),]);
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        return $this->app->make(MittensClient::class, [
            'client' => $client,
        ]);
    }

    /**
     * @throws MittensClientException
     * @throws JsonException
     */
    public function testItFiresTheMittensRequestDurationMeasuredEvent(): void
    {
        $mittensClient = $this->getMittensClient();
        Event::fake();
        $mittensClient->post(new MittensRequest(self::HTTPS_EXAMPLE_COM));
        Event::assertDispatched(MittensRequestDurationMeasured::class);
    }

    public function testItMeasuresTheCounter(): void
    {
        $duration = Duration::fromSeconds(
            $this->faker->randomNumber(1),
        );

        $this->mock(MetricRepository::class)
            ->expects('measureHistogram')
            ->withArgs(static function (HistogramMetric $event) use ($duration) {
                return $event->getName() === 'mittens:request_duration_seconds' &&
                    $event->getHelp() === 'The duration of a request to mittens' &&
                    $event->getValue() === $duration->inSeconds() &&
                    $event->getLabels()['uri'] === self::HTTPS_EXAMPLE_COM;
            });

        $event = new MittensRequestDurationMeasured(self::HTTPS_EXAMPLE_COM, $duration);
        $handler = $this->app->make(MittensRequestDurationMeasuredHandler::class);
        $handler->handle($event);
    }
}
