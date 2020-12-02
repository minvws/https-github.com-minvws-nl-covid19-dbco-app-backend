<?php

namespace App\Repositories;

use App\Models\CovidCase;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Used for registering a new case for pairing.
 *
 * @package App\Repositories
 */
class ApiCaseUpdateNotificationRepository implements CaseUpdateNotificationRepository
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
     * Trigger the healthauthority_api to come pick up the updated case
     *
     * @param CovidCase $case The case to pair.
     *
     * @returns true if API call succeeds, false otherwise
     */
    public function notify(CovidCase $case): bool
    {
        $options = [
            // No auth options for healthauthority_api
        ];

        try {
            $response = $this->client->post(sprintf('cases/%s/exports', $case->uuid), $options);
        } catch (\Throwable $t) {
            error_log("API error" . $t->getMessage());
            return false;
        }

        return true;
    }
}
