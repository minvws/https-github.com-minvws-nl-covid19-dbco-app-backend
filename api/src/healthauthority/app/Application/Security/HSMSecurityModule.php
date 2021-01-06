<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Hardware security module.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class HSMSecurityModule extends AbstractSecurityModule
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param bool            $usePhpRandomBytesForNonce
     * @param LoggerInterface $logger
     */
    public function __construct(bool $usePhpRandomBytesForNonce, LoggerInterface $logger)
    {
        parent::__construct($usePhpRandomBytesForNonce);
        $this->logger = $logger;
        $this->logger->debug(sprintf('HSMSecurityModule::__construct $usePhpRandomBytesForNonce = %s', $usePhpRandomBytesForNonce ? 'true' : 'false'));
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
        $this->logger->debug(sprintf('HSMSecurityModule::exec %s START', $command));

        $escapedCommand = escapeshellcmd(__DIR__ . '/../../../python/' . $command . '.py');
        $escapedArgs = array_map('escapeshellarg', $args);
        $template = '%s' . str_repeat(' %s', count($escapedArgs));

        $fullCommand = sprintf($template, $escapedCommand, ...$escapedArgs);
        $lastLine = exec($fullCommand, $fullOutput, $status);
        if ($status !== 0) {
            throw new RuntimeException('Error executing command "' . $fullCommand . ": " . $lastLine . print_r($fullOutput, true));
        }

        $this->logger->debug(sprintf('HSMSecurityModule::exec %s END (duration: %.5f)', $command, microtime(true) - $start));

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
        return hex2bin($this->exec('getrandombytes', $length));
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
