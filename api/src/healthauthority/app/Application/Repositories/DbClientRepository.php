<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeInterface;
use DBCO\HealthAuthorityAPI\Application\Security\EncryptionHelper;
use DBCO\HealthAuthorityAPI\Application\Models\Client;
use PDO;

/**
 * Used for registering / retrieving clients.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class DbClientRepository implements ClientRepository
{
    /**
     * @var PDO
     */
    private PDO $client;

    /**
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    /**
     * Constructor.
     *
     * @param PDO              $client
     * @param EncryptionHelper $encryptionHelper
     */
    public function __construct(PDO $client, EncryptionHelper $encryptionHelper)
    {
        $this->client = $client;
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * Seal value.
     *
     * @param string|null $value
     *
     * @return string|null
     */
    private function seal(?string $value): ?string
    {
        if ($value === null) {
            return null;
        } else {
            return $this->encryptionHelper->sealStoreValue($value);
        }
    }

    /**
     * Unseal value.
     *
     * @param string|null $value
     *
     * @return string|null
     */
    private function unseal(?string $value): ?string
    {
        if ($value === null) {
            return null;
        } else {
            return $this->encryptionHelper->unsealStoreValue($value);
        }
    }

    /**
     * @inheritDoc
     */
    public function registerClient(Client $client, DateTimeInterface $expiresAt)
    {
        $stmt = $this->client->prepare("
            INSERT INTO client (uuid, case_uuid, token, receive_key, transmit_key, created_at, updated_at)
            VALUES (:uuid, :caseUuid, :token, :receiveKey, :transmitKey, NOW(), NOW())
        ");

        $stmt->execute([
            'uuid' => $client->uuid,
            'caseUuid' => $client->caseUuid,
            'token' => $client->token,
            'receiveKey' => $this->seal($client->receiveKey),
            'transmitKey' => $this->seal($client->transmitKey)
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getClient(string $token): ?Client
    {
        $stmt = $this->client->prepare("
            SELECT uuid, case_uuid, receive_key, transmit_key
            FROM client
            WHERE token = :token
        ");

        $stmt->execute(['token' => $token]);
        $row = $stmt->fetchObject();

        if ($row === null) {
            return null;
        }

        return new Client(
            $row->uuid,
            $row->case_uuid,
            $token,
            $this->unseal($row->receive_key),
            $this->unseal($row->transmit_key)
        );
    }


    /**
     * Returns the paired clients for the given case.
     *
     * @param string $caseUuid
     *
     * @return Client[]
     */
    public function getClientsForCase(string $caseUuid): array
    {
        $stmt = $this->client->prepare("
            SELECT uuid, token, receive_key, transmit_key
            FROM client
            WHERE case_uuid = :caseUuid
        ");

        $stmt->execute(['caseUuid' => $caseUuid]);

        $clients = [];
        while ($row = $stmt->fetchObject()) {
            $clients[] = new Client(
                $row->uuid,
                $caseUuid,
                $row->token,
                $this->unseal($row->receive_key),
                $this->unseal($row->transmit_key)
            );
        }

        return $clients;
    }
}
