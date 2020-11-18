<?php

namespace App\Repositories;

use App\Models\CovidCase;
use DateTimeInterface;
use GuzzleHttp\Client as GuzzleClient;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Used for registering a new case for pairing.
 *
 * @package App\Repositories
 */
class ApiCaseExportRepository implements CaseExportRepository
{
    const JWT_EXPIRATION_TIME = 300; // 5 minutes

    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    /**
     * @var string
     */
    private string $jwtSecret;

    /**
     * Constructor.
     *
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client, string $jwtSecret)
    {
        $this->client = $client;
        $this->jwtSecret = $jwtSecret;
    }

    private function encodeJSON(CovidCase $case): array
    {
        $tasks = [];

        return [
            'dateOfSymptomOnset' => $case->dateOfSymptomOnset->format('c'),
            'tasks' => $tasks
        ];
    }

    /**
     * Encode JWT for registering case.
     *
     * @param string $caseUuid
     *
     * @return string
     */
    private function encodeJWT(string $caseUuid): string
    {
        $payload = array(
            "iat" => time(),
            "exp" => time() + self::JWT_EXPIRATION_TIME,
            "http://ggdghor.nl/cid" => $caseUuid
        );

        return JWT::encode($payload, $this->jwtSecret);
    }

    /**
     * Fetch pairing code for the given case.case_id
     *
     * @param CovidCase $case The case to pair.
     *
     * @throws GuzzleException
     */
    public function export(CovidCase $case): void
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->encodeJWT($case->uuid)
            ],
            'json' => $this->encodeJSON($case)
        ];

        error_log(var_export($options, true));

        try {
            $response = $this->client->post('cases', $options);
        } catch (\Throwable $t) {
            print_r($t->getMessage());
        }
    }
}
