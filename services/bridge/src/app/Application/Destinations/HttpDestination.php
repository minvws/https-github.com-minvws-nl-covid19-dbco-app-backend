<?php
namespace DBCO\Bridge\Application\Destinations;

use DBCO\Bridge\Application\Models\Request;
use DBCO\Bridge\Application\Models\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * HTTP destination.
 *
 * @package App\Application\Destinations
 */
class HttpDestination implements Destination
{
    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    /**
     * @var string
     */
    private string $method;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var callable|null
     */
    private $requestModifier;

    /**
     * @var callable|null
     */
    private $responseModifier;

    /**
     * Constructor.
     *
     * @param GuzzleClient    $client
     * @param string          $method
     * @param string          $path
     * @param LoggerInterface $logger
     */
    public function __construct(GuzzleClient $client, string $method, string $path, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->method = $method;
        $this->path = $path;
        $this->logger = $logger;
    }

    /**
     * Set request modifier callback.
     *
     * The request modifier will be called with the following parameters:
     * - Psr\Http\Message\RequestInterface $httpRequest
     * - DBCO\Bridge\Application\Models\Request $request
     *
     * And should return a Psr\Http\Message\RequestInterface object.
     *
     * @param callable|null $requestModifier
     */
    public function setRequestModifier(?callable $requestModifier)
    {
        $this->requestModifier = $requestModifier;
    }

    /**
     * Set response modifier callback.
     *
     * The response modifier will be called with the following parameters:
     * - Psr\Http\Message\ResponseInterface $httpResponse
     * - Psr\Http\Message\RequestInterface $httpRequest
     * - DBCO\Bridge\Application\Models\Request $request
     *
     * And should return a Psr\Http\Message\ResponseInterface object.
     *
     * @param callable|null $responseModifier
     */
    public function setResponseModifier(?callable $responseModifier)
    {
        $this->responseModifier = $responseModifier;
    }

    /**
     * Build path.
     *
     * @param Request $request
     */
    private function buildPath(Request $request)
    {
        $path = $this->path;

        foreach ($request->params as $key => $value) {
            $path = str_replace('{' . $key . '}', urlencode($value), $path);
        }

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(Request $request): Response
    {
        $path = $this->buildPath($request);

        $this->logger->debug('Send HTTP request ' . strtoupper($this->method) . ' ' . $path);

        $httpRequest = new GuzzleRequest($this->method, $path);
        $httpRequest->getBody()->write($request->data);

        try {
            if (is_callable($this->requestModifier)) {
                $httpRequest = call_user_func($this->requestModifier, $httpRequest, $request);
            }

            $httpResponse = $this->client->send($httpRequest);
            $this->logger->debug("HTTP response:\n" . (string)$httpResponse->getBody());
            if (is_callable($this->responseModifier)) {
                $httpResponse = call_user_func($this->responseModifier, $httpResponse, $httpRequest, $request);
            }

            return new Response(Response::SUCCESS, (string)$httpResponse->getBody());
        } catch (BadResponseException $e) {
            $this->logger->error('Error sending HTTP request: ' . $e->getMessage());

            $httpResponse = $e->getResponse();
            $this->logger->debug("HTTP response:\n" . (string)$httpResponse->getBody());
            if (is_callable($this->responseModifier)) {
                $httpResponse = call_user_func($this->responseModifier, $httpResponse, $httpRequest, $request);
            }

            return new Response(Response::ERROR, (string)$httpResponse->getBody());
        } catch (Throwable $e) {
            $this->logger->error('Error sending HTTP request: ' . $e->getMessage());
            throw $e;
        }
    }
}
