<?php
namespace MinVWS\HealthCheck\Checks;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Perform a health check through a HTTP request.
 *
 * @package MinVWS\HealthCheck\Checks
 */
class GuzzleHealthCheck extends HttpClientHealthCheck
{
    /**
     * @var array
     */
    private array $options;

    /**
     * Constructor.
     *
     * @param GuzzleClient  $client
     */
    public function __construct(GuzzleClient $client, string $method, string $uri, array $headers = [], array $options = [])
    {
        parent::__construct($client, new Request($method, $uri, $headers));

        $defaultOptions = [
            'connect_timeout' => 5,
            'read_timeout' => 5,
            'timeout' => 15
        ];

        $this->options = array_merge($defaultOptions, $options);
    }

    /**
     * @inheritdoc
     */
    protected function sendRequest(RequestInterface $request): ResponseInterface
    {
        /** @var $client GuzzleClient */
        $client = $this->client;
        return $client->send($request, $this->options);
    }
}