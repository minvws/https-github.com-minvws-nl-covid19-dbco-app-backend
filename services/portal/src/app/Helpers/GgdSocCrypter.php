<?php

declare(strict_types=1);

namespace App\Helpers;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use SodiumException;

use function array_map;
use function random_bytes;
use function sodium_base642bin;
use function sodium_bin2base64;
use function sodium_crypto_box;
use function sodium_crypto_box_keypair;
use function sodium_crypto_box_keypair_from_secretkey_and_publickey;
use function sodium_crypto_box_publickey;
use function sodium_crypto_box_secretkey;

use const SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;
use const SODIUM_CRYPTO_BOX_NONCEBYTES;

class GgdSocCrypter
{
    private Closure $randomBytes;
    private Closure $crypt;

    public function __construct(
        private readonly string $senderSecretKey,
        private readonly string $receiverPublicKey,
        ?callable $randomBytes = null,
        ?callable $crypt = null,
    ) {
        $this->randomBytes = $randomBytes ? Closure::fromCallable($randomBytes) : random_bytes(...);
        $this->crypt = $crypt ? Closure::fromCallable($crypt) : sodium_crypto_box(...);
    }

    public static function withBase64Keys(
        string $base64SenderSecretKey,
        string $base64ReceiverPublicKey,
        ?callable $randomBytes = null,
        ?callable $crypt = null,
    ): self {
        $senderSecretKey = self::decodeBase64KeyOrGenerate($base64SenderSecretKey, "GGD SOC sender secret not set, using generated");
        $receiverPublicKey = self::decodeBase64KeyOrGenerate(
            $base64ReceiverPublicKey,
            "GGD SOC receiver public key not set, using generated",
        );
        return new self($senderSecretKey, $receiverPublicKey, $randomBytes, $crypt);
    }

    /**
     * @throws SodiumException
     */
    private static function decodeBase64KeyOrGenerate(string $base64Key, string $error): string
    {
        if ($base64Key === '') {
            Log::error($error);
            return self::generateKeySet()['secret'];
        }
        return sodium_base642bin($base64Key, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    public function encrypt(string $data): string
    {
        $keyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey($this->senderSecretKey, $this->receiverPublicKey);
        $nonce = ($this->randomBytes)(SODIUM_CRYPTO_BOX_NONCEBYTES);
        return $nonce . ($this->crypt)($data, $nonce, $keyPair);
    }

    /**
     * @return array{public: string, secret: string}
     *
     * @throws SodiumException
     */
    public static function generateKeySet(): array
    {
        $key = sodium_crypto_box_keypair();
        $public = sodium_crypto_box_publickey($key);
        $secret = sodium_crypto_box_secretkey($key);
        return [
            'public' => $public,
            'secret' => $secret,
        ];
    }

    public static function encodeKeyToBase64(string $binaryKey): string
    {
        return sodium_bin2base64($binaryKey, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }

    public static function generateBase64KeySet(): array
    {
        $keys = self::generateKeySet();
        return array_map(self::encodeKeyToBase64(...), $keys);
    }
}
