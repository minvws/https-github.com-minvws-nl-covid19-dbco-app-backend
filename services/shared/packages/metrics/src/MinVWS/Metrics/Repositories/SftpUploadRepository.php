<?php
namespace MinVWS\Metrics\Repositories;

use MinVWS\Metrics\Models\Export;

/**
 * Responsible for uploading exports via SFTP.
 *
 * @package MinVWS\Metrics\Repositories
 */
class SftpUploadRepository implements UploadRepository
{
    /**
     * Upload the file at the given path.
     *
     * @param string $path
     * @param Export $export
     */
    public function uploadFile(string $path, Export $export)
    {

    }
}