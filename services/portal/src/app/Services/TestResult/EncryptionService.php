<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use Exception;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use SodiumException;

use function base64_decode;
use function is_string;

final class EncryptionService
{
    public function __construct(
        private readonly EncryptionHelper $encryptionHelper,
    ) {
    }

    /**
     * @throws Exception
     * @throws SodiumException
     */
    public function decryptPayload(string $payload): string
    {
        $payloadData = base64_decode($payload, true);

        if (!is_string($payloadData)) {
            throw new Exception('payload can not be base64-decoded');
        }

        return $this->encryptionHelper->unsealDataWithKey($payloadData, SecurityModule::SK_TEST_RESULT);
    }
}
