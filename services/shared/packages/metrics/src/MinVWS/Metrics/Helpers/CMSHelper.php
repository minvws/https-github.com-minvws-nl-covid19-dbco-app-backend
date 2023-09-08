<?php

namespace MinVWS\Metrics\Helpers;

use Exception;
use MinVWS\Metrics\Models\ExportConfig\Encryption;
use MinVWS\Metrics\Models\ExportConfig\Signature;
use Psr\Log\LoggerInterface;

/**
 * Helper for encrypting/signing data using the OpenSSL CMS packaging format.
 *
 * @package MinVWS\Metrics\Helpers
 */
class CMSHelper
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Encrypts the given data using the given encryption config.
     *
     * @param string     $data
     * @param Encryption $config
     *
     * @return string
     * @throws Exception
     */
    public function encrypt(string $data, Encryption $config): string
    {
        $command = [
            'openssl',
            'cms',
            '-encrypt',
            '-' . $config->cipher,
            '-outform', 'PEM',
            $config->certPath,
        ];

        try {
            return $this->exec($command, $data);
        } catch (Exception $e) {
            $this->logger->error('Encryption failed: ' . $e->getMessage());
            throw new Exception('Encryption failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Signs the given data using the given signing config.
     *
     * @param string    $data
     * @param Signature $config
     *
     * @return string
     * @throws Exception
     */
    public function sign(string $data, Signature $config): string
    {
        $command = [
            'openssl',
            'cms',
            '-sign',
            '-signer', $config->certPath,
            '-inkey', $config->privateKeyPath,
            '-outform', 'DER'
        ];

        if (!empty($config->privateKeyPassphrase)) {
            $command[] = '-passin';
            $command[] = 'pass:' . $config->privateKeyPassphrase;
        }

        try {
            return $this->exec($command, $data);
        } catch (Exception $e) {
            $this->logger->error('Signing failed: ' . $e->getMessage());
            throw new Exception('Signing failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Executes the given command, pipes in the given input, captures and returns the output.
     *
     * NOTE: we use an array for the command; PHP (>= 7.4) will automatically escape the arguments
     *
     * @param array  $command
     * @param string $input
     *
     * @return string
     * @throws Exception
     */
    private function exec(array $command, string $input): string
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $pipes = [];
        $process = proc_open($command, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            throw new Exception('Error starting child process');
        }

        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        $returnValue = proc_close($process);
        if ($returnValue !== 0) {
            throw new Exception($error);
        }

        return $output;
    }
}
