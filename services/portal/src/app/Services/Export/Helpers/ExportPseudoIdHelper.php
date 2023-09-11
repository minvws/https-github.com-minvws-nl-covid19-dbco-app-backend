<?php

declare(strict_types=1);

namespace App\Services\Export\Helpers;

use App\Models\Export\ExportClient;
use App\Services\Export\Exceptions\ExportPseudoIdException;
use App\Services\Export\Exceptions\ExportRuntimeException;
use SodiumException;

use function sodium_base642bin;
use function sodium_bin2base64;
use function sodium_crypto_box;
use function sodium_crypto_box_open;

use const SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

class ExportPseudoIdHelper
{
    /**
     * @throws ExportPseudoIdException
     */
    public function pseudoIdToIdForClient(string $pseudoId, ExportClient $client): string
    {
        try {
            $bin = sodium_base642bin($pseudoId, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
            $value = sodium_crypto_box_open($bin, $client->pseudo_id_nonce, $client->pseudo_id_key_pair);

            if ($value === false) {
                throw new ExportPseudoIdException();
            }

            return $value;

        // @codeCoverageIgnoreStart
        } catch (SodiumException $e) {
            throw ExportRuntimeException::from($e);
        }
        // @codeCoverageIgnoreEnd
    }

    public function idToPseudoIdForClient(string $value, ExportClient $client): string
    {
        try {
            $bin = sodium_crypto_box($value, $client->pseudo_id_nonce, $client->pseudo_id_key_pair);
            return sodium_bin2base64($bin, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);

        // @codeCoverageIgnoreStart
        } catch (SodiumException $e) {
            throw ExportRuntimeException::from($e);
        }
        // @codeCoverageIgnoreEnd
    }
}
