<?php
declare(strict_types=1);

namespace DBCO\Application\Repositories;

use DBCO\Application\Models\DbcoCase;
use Exception;

/**
 * Store / retrieve case information.
 */
interface CaseRepository
{
    /**
     * Create case.
     *
     * @param DbcoCase $case
     *
     * @return DbcoCase
     *
     * @throws Exception
     */
    public function createCase(DbcoCase $case);
}
