<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeInterface;
use DateTimeZone;
use DBCO\Shared\Application\Models\SealedData;
use Firebase\JWT\JWT;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * Used for exporting the case to the sluice.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class ApiCaseExportRepository implements CaseExportRepository
{
    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string
     */
    private string $jwtSecret;

    /**
     * Constructor.
     *
     * @param GuzzleClient    $client
     * @param LoggerInterface $logger
     * @param string          $jwtSecret
     */
    public function __construct(GuzzleClient $client, LoggerInterface $logger, string $jwtSecret)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * @inheritDoc
     */
    public function exportCase(string $token, SealedData $sealedCase, DateTimeInterface $expiresAt)
    {
        $this->logger->debug('Export case to private API');

        $payload = array(
            "iat" => time(),
            "exp" => time() + 300,
            "http://ggdghor.nl/token" => $token
        );

        $jwt = JWT::encode($payload, $this->jwtSecret);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt
            ],
            'json' => [
                'expiresAt' =>
                    $expiresAt
                        ->setTimezone(new DateTimeZone('UTC'))
                        ->format('Y-m-d\TH:i:s\Z'),
                'sealedCase' => [
                    'ciphertext' => base64_encode($sealedCase->ciphertext),
                    'nonce' => base64_encode($sealedCase->nonce)
                ]
            ]
        ];

        try {
            $response = $this->client->put('cases/' . $token, $options);
            $this->logger->debug("Response status:" . $response->getStatusCode());
        } catch (Throwable $e) {
            $this->logger->error('Error exporting case to private API: ' . $e->getMessage());
            if ($e instanceof BadResponseException) {
                $this->logger->debug("Response:\n" . (string)$e->getResponse()->getBody());
            }
            throw new RuntimeException('Error exporting case to private API');
        }
    }
}
