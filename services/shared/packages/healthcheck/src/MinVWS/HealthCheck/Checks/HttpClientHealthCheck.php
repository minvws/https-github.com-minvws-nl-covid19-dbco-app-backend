<?php
namespace MinVWS\HealthCheck\Checks;

use MinVWS\HealthCheck\Models\HealthCheckResult;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Perform a health check through a HTTP request.
 *
 * @package MinVWS\HealthCheck\Checks
 */
class HttpClientHealthCheck implements HealthCheck
{
    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var int
     */
    protected int $expectedResponseStatusCode = 200;

    /**
     * @var string
     */
    protected ?string $expectedResponseBody = null;

    /**
     * Constructor.
     *
     * @param ClientInterface  $client
     * @param RequestInterface $request
     */
    public function __construct(ClientInterface $client, RequestInterface $request)
    {
        $this->client = $client;
        $this->request = $request;
    }

    /**
     * Sets the expected response status code (default: 200).
     *
     * @param int $code
     *
     * @return self
     */
    public function setExpectedResponseStatusCode(int $code)
    {
        $this->expectedResponseStatusCode = $code;
        return $this;
    }

    /**
     * Sets the expected response body (default: no check).
     *
     * @param string $body
     *
     * @return self
     */
    public function setExpectedResponseBody(string $body)
    {
        $this->expectedResponseBody = $body;
        return $this;
    }

    /**
     * Send request.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface
     */
    protected function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function performHealthCheck(): HealthCheckResult
    {
        try {
            $response = $this->sendRequest($this->request);

            if ($response->getStatusCode() !== $this->expectedResponseStatusCode) {
                return new HealthCheckResult(
                    false,
                    'invalidResponseStatusCode',
                    sprintf('Response status %d != %d', $response->getStatusCode(), $this->expectedResponseStatusCode)
                );
            }

            if ($this->expectedResponseBody !== null && (string)$response->getBody() !== $this->expectedResponseBody) {
                return new HealthCheckResult(
                    false,
                    'invalidResponseBody',
                    sprintf('Response body "%s" != "%s"', (string)$response->getBody(), $this->expectedResponseBody)
                );
            }

            return new HealthCheckResult(true);
        } catch (ClientExceptionInterface $e) {
            return new HealthCheckResult(
                false,
                'unknownError',
                $e->getMessage()
            );
        }
    }
}