<?php
namespace MinVWS\Metrics\Repositories;

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
     */
    public function uploadFile(string $path, Export $export);
}