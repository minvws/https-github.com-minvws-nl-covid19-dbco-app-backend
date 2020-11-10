<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

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
     * @param Client $client Client.
     */
    public function registerClient(Client $client);

    /**
     * Get client details.
     *
     * @param string $token
     *
     * @return Client|null
     */
    public function getClient(string $token): ?Client;
}
