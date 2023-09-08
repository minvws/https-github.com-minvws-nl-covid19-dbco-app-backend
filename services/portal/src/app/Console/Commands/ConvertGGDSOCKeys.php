<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\GgdSocCrypter;
use Illuminate\Console\Command;
use SodiumException;
use Webmozart\Assert\Assert;

use function sodium_base642bin;
use function sodium_hex2bin;
use function strlen;

use const SODIUM_BASE64_VARIANT_ORIGINAL;
use const SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING;
use const SODIUM_BASE64_VARIANT_URLSAFE;
use const SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;
use const SODIUM_CRYPTO_BOX_PUBLICKEYBYTES;

class ConvertGGDSOCKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:ggdsockeys {key : The key that needs to be converted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert a (public) key (for audit log exchange with GGD SOC) to the expected format';

    public function handle(): int
    {
        $originalKey = $this->argument('key');
        Assert::string($originalKey);
        $binaryKey = $this->hexDecode($originalKey);
        if (!$binaryKey) {
            $binaryKey = $this->base64Decode($originalKey);
        }
        if (!$binaryKey) {
            $this->error("Key is not a hex or base64 encoded key.");
            return self::FAILURE;
        }
        if (strlen($binaryKey) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) {
            $this->error("Key does not have the expected length.");
            return self::FAILURE;
        }
        $encodedKey = GgdSocCrypter::encodeKeyToBase64($binaryKey);
        $this->info("Re-encoded key (URL-safe Base64 encoding, without = padding characters)");
        $this->info($encodedKey);
        return self::SUCCESS;
    }

    private function hexDecode(string $key): ?string
    {
        try {
            return sodium_hex2bin($key);
        } catch (SodiumException) {
            return null;
        }
    }

    private function base64Decode(string $key): ?string
    {
        $encodingTypes = [SODIUM_BASE64_VARIANT_ORIGINAL, SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING, SODIUM_BASE64_VARIANT_URLSAFE, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING];
        foreach ($encodingTypes as $encodingType) {
            try {
                return sodium_base642bin($key, $encodingType);
            } catch (SodiumException) {
                continue;
            }
        }
        return null;
    }
}
