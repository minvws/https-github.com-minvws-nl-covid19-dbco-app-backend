<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Security;

use RuntimeException;

/**
 * Hardware security module.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Security
 */
class HSMSecurityModule implements SecurityModule
{
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
        $escapedCommand = escapeshellcmd(__DIR__ . '/../../../python/' . $command . '.py');
        $escapedArgs = array_map('escapeshellarg', $args);
        $template = '%s' . str_repeat(' %s', count($escapedArgs));

        $lastLine = exec(sprintf($template, $escapedCommand, ...$escapedArgs), $fullOutput, $status);
        if ($status !== 0) {
            throw new RuntimeException('Error executing command "' . $command . ": " . $lastLine);
        }

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
}
