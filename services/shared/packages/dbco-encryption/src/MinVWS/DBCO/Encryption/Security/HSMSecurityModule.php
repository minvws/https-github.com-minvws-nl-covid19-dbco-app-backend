<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Encryption\Security;

use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Hardware security module.
 */
class HSMSecurityModule implements SecurityModule
{
    private const DEBUG = false;

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
     * Log debug message.
     *
     * @param string $message
     */
    private function debug(string $message)
    {
        if (self::DEBUG) {
            $this->logger->debug($message);
        }
    }

    /**
     * Execute command.
     *
     * @param string $command Command name.
     * @param array  $args    Command arguments.
     *
     * @return string Last output line.
     */
    private function exec(string $command, ...$args): string
    {
        $start = microtime(true);
        $this->debug(sprintf('HSMSecurityModule::exec %s %s START', $command, implode(',', $args)));

        $escapedCommand = escapeshellcmd(__DIR__ . '/../../../../python/' . $command . '.py');
        $escapedArgs = array_map('escapeshellarg', $args);
        $template = '%s' . str_repeat(' %s', count($escapedArgs));

        $fullCommand = sprintf($template, $escapedCommand, ...$escapedArgs);
        $lastLine = exec($fullCommand, $fullOutput, $status);
        if ($status !== 0) {
            throw new RuntimeException('Error executing command "' . $fullCommand . ": " . $lastLine . print_r($fullOutput, true));
        }

        $this->debug(sprintf('HSMSecurityModule::exec %s END (duration: %.5f)', $command, microtime(true) - $start));

        return $lastLine;
    }

    /**
     * @inheritdoc
     */
    public function generateSecretKey(string $identifier): string
    {
        $seed = hex2bin($this->exec('createkeyaes', $identifier));
        $keypair = sodium_crypto_box_seed_keypair($seed);
        return sodium_crypto_box_secretkey($keypair);
    }

    /**
     * @inheritdoc
     */
    public function hasSecretKey(string $identifier): bool
    {
        try {
            $this->exec('getkeyaes', $identifier);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getSecretKey(string $identifier): string
    {
        $seed = hex2bin($this->exec('getkeyaes', $identifier));
        $keypair = sodium_crypto_box_seed_keypair($seed);
        return sodium_crypto_box_secretkey($keypair);
    }

    /**
     * @inheritdoc
     */
    public function deleteSecretKey(string $identifier): void
    {
        $this->exec('deletekeyaes', $identifier);
    }

    /**
     * @inheritdoc
     */
    public function randomBytes(int $length): string
    {
        // We used to use: hex2bin($this->exec('getrandombytes', $length));
        // However creating a connection to the HSM adds a lot of overhead
        // and after extensive testing PHP's random_bytes seems just as random
        // even using a container on a VM.
        return random_bytes($length);
    }

    /**
     * @inheritdoc
     */
    public function listSecretKeys(): array
    {
        try {
            $keys = @json_decode($this->exec('listkeysaes'));
            return is_array($keys) ? $keys : [];
        } catch (Exception $e) {
            return [];
        }
    }
}
