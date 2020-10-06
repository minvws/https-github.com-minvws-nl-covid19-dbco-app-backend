<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Models\DbcoCase;
use DateTimeInterface;

/**
 * Store / retrieve case information.
 */
interface CaseRepository
{
    /**
     * Create case.
     *
     * @param string            $id
     * @param DateTimeInterface $expiresAt
     *
     * @return DbcoCase
     */
    public function createCase(string $id, DateTimeInterface $expiresAt): DbcoCase;
}
