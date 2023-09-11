<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Intake;

use MinVWS\DBCO\Encryption\Security\SecurityCache;
use MinVWS\DBCO\Encryption\Security\SecurityModule;
use Tests\Feature\FeatureTestCase;

use function base64_encode;
use function config;
use function json_encode;
use function random_bytes;
use function sodium_crypto_box;
use function sodium_crypto_box_keypair;
use function sodium_crypto_box_keypair_from_secretkey_and_publickey;
use function sodium_crypto_box_publickey;
use function sodium_crypto_box_publickey_from_secretkey;
use function sodium_crypto_box_seal;
use function sodium_crypto_box_secretkey;

use const SODIUM_CRYPTO_BOX_NONCEBYTES;

class IntakeFeatureTestCase extends FeatureTestCase
{
    private string $intakePublicKey;
    private string $mittensPrivateKey;

    protected function setUp(): void
    {
        parent::setUp();

        $securityCache = $this->app->get(SecurityCache::class);
        $intakePrivateKey = $securityCache->getSecretKey(SecurityModule::SK_PUBLIC_PORTAL);
        $this->intakePublicKey = sodium_crypto_box_publickey_from_secretkey($intakePrivateKey);

        $mittensKeypair = sodium_crypto_box_keypair();
        $this->mittensPrivateKey = sodium_crypto_box_secretkey($mittensKeypair);
        $mittensPublicKey = sodium_crypto_box_publickey($mittensKeypair);
        config()->set('misc.intake.identity_data_public_key', base64_encode($mittensPublicKey));
    }

    protected function encryptIdentityData(array $identityData): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        $encryptionKey = sodium_crypto_box_keypair_from_secretkey_and_publickey($this->mittensPrivateKey, $this->intakePublicKey);

        $encrypted = sodium_crypto_box(json_encode($identityData), $nonce, $encryptionKey);
        return base64_encode($nonce . $encrypted);
    }

    protected function encryptIntakeData(array $intakeData): string
    {
        return base64_encode(sodium_crypto_box_seal(json_encode($intakeData), $this->intakePublicKey));
    }

    protected function encryptHandoverData(?array $handoverData): ?string
    {
        if ($handoverData === null) {
            return null;
        }

        return base64_encode(sodium_crypto_box_seal(json_encode($handoverData), $this->intakePublicKey));
    }
}
