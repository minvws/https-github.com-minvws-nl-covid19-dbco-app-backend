<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DateTimeInterface;
use DBCO\Shared\Application\Models\SealedData;

/**
 * Used for exporting the case to the sluice.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
interface CaseExportRepository
{
    /**
     * Export case.
     *
     * @param string            $token     Shared token identifier.
     * @param SealedData        $case      Case data.
     * @param DateTimeInterface $expiresAt Expiry date for this data.
     */
    public function exportCase(string $token, SealedData $case, DateTimeInterface $expiresAt);
}
