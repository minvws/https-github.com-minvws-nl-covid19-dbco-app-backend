<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeInterface;
use DBCO\HealthAuthorityAPI\Application\Models\Client;

/**
 * Used for registering / retrieving clients.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
interface ClientRepository
{
    /**
     * Register client.
     *
     * @param Client            $client    Client.
     * @param DateTimeInterface $expiresAt Client expiry.
     */
    public function registerClient(Client $client, DateTimeInterface $expiresAt);

    /**
     * Get client details.
     *
     * @param string $token
     *
     * @return Client|null
     */
    public function getClient(string $token): ?Client;

    /**
     * Returns the paired clients for the given case.
     *
     * @param string $caseUuid
     *
     * @return Client[]
     */
    public function getClientsForCase(string $caseUuid): array;
}
