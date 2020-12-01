<?php

namespace App\Repositories;

use App\Models\CovidCase;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Used for registering a new case for pairing.
 *
 * @package App\Repositories
 */
class ApiCaseExportRepository implements CaseExportRepository
{
    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    /**
     * Constructor.
     *
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Fetch pairing code for the given case.case_id
     *
     * @param CovidCase $case The case to pair.
     *
     * @returns true if API call succeeds, false otherwise
     */
    public function export(CovidCase $case): bool
    {
        $options = [
            // No auth options for healthauthority_api
        ];

        try {
            $response = $this->client->post(sprintf('cases/%s/exports', $case->uuid), $options);
        } catch (\Throwable $t) {
            error_log("API error" . $t->getMessage());
        }
    }
}
