<?php

namespace MinVWS\Metrics\Repositories;

use Exception;
use MinVWS\Metrics\Models\Export;

/**
 * Responsible for uploading exports.
 *
 * @package MinVWS\Metrics\Repositories
 */
interface UploadRepository
{
    /**
     * Upload the file at the given path.
     *
     * @param string $path
     * @param Export $export
     *
     * @throws Exception
     */
    public function uploadFile(string $path, Export $export);
}
