<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

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
     * @param string     $token Shared token identifier.
     * @param SealedData $case  Case data.
     */
    public function exportCase(string $token, SealedData $case);
}
