<?php

namespace MinVWS\Metrics\Repositories;

use Exception;
use MinVWS\Metrics\Models\Export;
use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\PublicKeyLoader;

/**
 * Responsible for uploading exports via SFTP.
 *
 * @package MinVWS\Metrics\Repositories
 */
class SftpUploadRepository implements UploadRepository
{
    /**
     * @var string
     */
    private string $hostname;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $privateKey;

    /**
     * @var string|null
     */
    private ?string $passphrase;

    /**
     * @var string
     */
    private string $uploadPath;

    /**
     * Constructor.
     * @param string      $hostname
     * @param string      $username
     * @param string      $privateKey
     * @param string|null $passphrase
     * @param string      $uploadPath
     */
    public function __construct(string $hostname, string $username, string $privateKey, ?string $passphrase, string $uploadPath)
    {
        $this->hostname = $hostname;
        $this->username = $username;
        $this->privateKey = $privateKey;
        $this->passphrase = $passphrase;
        $this->uploadPath = rtrim($uploadPath, '/');
    }

    /**
     * @inheritDoc
     */
    public function uploadFile(string $path, Export $export)
    {
        $keyData = is_file($this->privateKey) ? file_get_contents($this->privateKey) : $this->privateKey;
        $key = PublicKeyLoader::load($keyData, $this->passphrase ?? false);

        $sftp = new SFTP($this->hostname);
        if (!$sftp->login($this->username, $key)) {
            throw new Exception('Failed to login!');
        }

        $remotePath = $this->uploadPath . '/' . basename($path);
        if (!$sftp->put($remotePath, $path, SFTP::SOURCE_LOCAL_FILE)) {
            throw new Exception('Failed to upload file!');
        }
    }
}
