<?php

namespace DBCO\Shared\Application\Bridge;

use Predis\Client as PredisClient;
use Throwable;

/**
 * Client for the bridge.
 */
class Client
{
    /**
     * Redis client.
     *
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * Constructor.
     *
     * @param PredisClient $client
     */
    public function __construct(PredisClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send request.
     *
     * @param Request $request
     *
     * @return Response|null
     *
     * @throws TimeoutException
     * @throws RequestException
     * @throws ClientException
     */
    public function request(Request $request): ?Response
    {
        $requestData = [
            'params' => $request->getParams(),
            'data' => $request->getData()
        ];

        $responseKey = $request->getResponseKey();
        if (!empty($responseKey)) {
            $requestData['responseKey'] = $responseKey;
        }

        try {
            $this->client->rpush($request->getKey(), [json_encode($requestData)]);

            if (empty($responseKey)) {
                // don't care about the result
                return null;
            }

            $result = $this->client->blpop($responseKey, $request->getTimeout());
            if ($result === null || count($result) !== 2) {
                throw new TimeoutException('Request timeout');
            }

            $responseData = json_decode($result[1]);
            $response = new Response($responseData->data);

            if ($responseData->status === 'SUCCESS') {
                return $response;
            } else {
                throw new RequestException('Request failed', $response);
            }
        } catch (TimeoutException $e) {
            throw $e;
        } catch (RequestException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ClientException('Client error: ' . $e->getMessage(), 0, $e);
        }
    }
}
