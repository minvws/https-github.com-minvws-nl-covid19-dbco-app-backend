<?php

declare(strict_types=1);

namespace App\Http\Client\Guzzle;

use App\Events\Mittens\MittensRequestDurationMeasured;
use App\Http\CircuitBreaker\Exceptions\NotAvailableException;
use App\Http\Requests\Mittens\MittensRequest;
use App\Models\Metric\Mittens\MittensRequest as MittensRequestMetric;
use App\Services\MetricService;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use JsonException;
use MinVWS\Timer\Timer;
use Psr\Log\LoggerInterface;
use Throwable;

use function is_array;
use function is_object;
use function json_decode;
use function property_exists;

use const JSON_THROW_ON_ERROR;

final class MittensClient implements MittensClientInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly MetricService $metricService,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws MittensClientException
     * @throws JsonException
     */
    public function post(MittensRequest $mittensRequest): object
    {
        $this->logger->info('Trying to perform a Mittens request', ['url' => $mittensRequest->url]);

        try {
            $timer = Timer::start();
            $response = $this->client->send($mittensRequest->toGuzzleRequest());
            $duration = $timer->stop();
            MittensRequestDurationMeasured::dispatch($mittensRequest->url, $duration);
        } catch (ClientException $clientException) {
            $this->handleClientException($clientException, $mittensRequest);
            return $this->retryPost($clientException, $mittensRequest);
        } catch (ServerException $serverException) {
            $this->handleServerException($serverException, $mittensRequest);
            return $this->retryPost($serverException, $mittensRequest);
        } catch (NotAvailableException $notAvailableException) {
            $this->handleNotAvailableException($notAvailableException, $mittensRequest);
            throw MittensClientException::fromThrowable($notAvailableException);
        } catch (ConnectException $connectException) {
            $this->handleConnectException($connectException, $mittensRequest);
            throw MittensClientException::fromThrowable($connectException);
        } catch (GuzzleException $guzzleException) {
            $this->handleGuzzleException($guzzleException, $mittensRequest);
            throw MittensClientException::fromThrowable($guzzleException);
        }

        $this->logger->info('Request to Mittens succeeded');

        $this->metricService->measure(
            MittensRequestMetric::response($mittensRequest->url, $response->getStatusCode()),
        );

        try {
            return (object) json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw MittensClientException::fromThrowable($jsonException);
        }
    }

    /**
     * @throws MittensClientException
     */
    private function handleClientException(ClientException $clientException, MittensRequest $mittensRequest): void
    {
        $response = $clientException->getResponse();

        try {
            $responseData = json_decode((string) $response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw MittensClientException::fromThrowable($jsonException);
        }

        $this->metricService->measure(
            MittensRequestMetric::response($mittensRequest->url, $response->getStatusCode()),
        );

        if (
            is_object($responseData)
            && property_exists($responseData, 'errors')
            && is_array($responseData->errors)
        ) {
            $this->logger->info(
                'Request to Mittens failed with error in response',
                [
                    'error' => $responseData->errors[0],
                    'statusCode' => $clientException->getCode(),
                ],
            );

            throw new MittensClientException($responseData->errors[0], $clientException->getCode(), $clientException);
        }

        $this->logger->info(
            'Request to Mittens failed without errors in response',
            [
                'statusCode' => $clientException->getCode(),
            ],
        );
    }

    /**
     * @throws MittensClientException
     * @throws JsonException
     */
    private function retryPost(Throwable $exception, MittensRequest $request): object
    {
        if (!$request->isRetryAllowed()) {
            throw new MittensClientException('service unavailable', $exception->getCode(), $exception);
        }

        //Service unavailable.. retry request
        $request->updateRequestCounter();
        return $this->post($request);
    }

    private function handleServerException(ServerException $serverException, MittensRequest $mittensRequest): void
    {
        $response = $serverException->getResponse();

        $this->metricService->measure(
            MittensRequestMetric::response($mittensRequest->url, $response->getStatusCode()),
        );

        $this->logger->error(
            'Request to Mittens failed',
            [
                'message' => $serverException->getMessage(),
                'statusCode' => $response->getStatusCode(),
            ],
        );
    }

    private function handleConnectException(ConnectException $connectException, MittensRequest $mittensRequest): void
    {
        $this->logger->error(
            'Request to Mittens failed due to an connection error',
            [
                'message' => $connectException->getMessage(),
            ],
        );

        $this->metricService->measure(
            MittensRequestMetric::connectionError($mittensRequest->url),
        );
    }

    private function handleGuzzleException(GuzzleException $guzzleException, MittensRequest $mittensRequest): void
    {
        $this->logger->error(
            'Request to Mittens failed',
            [
                'message' => $guzzleException->getMessage(),
                'statusCode' => $guzzleException->getCode(),
            ],
        );

        $this->metricService->measure(
            MittensRequestMetric::response($mittensRequest->url, $guzzleException->getCode()),
        );
    }

    private function handleNotAvailableException(
        NotAvailableException $notAvailableException,
        MittensRequest $mittensRequest,
    ): void {
        $this->logger->error(
            'Request to Mittens intercepted',
            [
                'message' => $notAvailableException->getMessage(),
            ],
        );

        $this->metricService->measure(
            MittensRequestMetric::circuitBreakerIntercepted($mittensRequest->url),
        );
    }
}
