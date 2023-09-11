<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Export\ExportClient;
use Illuminate\Database\Eloquent\Factories\Factory;

use function random_bytes;
use function sodium_crypto_box_seed_keypair;

use const SODIUM_CRYPTO_BOX_NONCEBYTES;
use const SODIUM_CRYPTO_BOX_SEEDBYTES;

class ExportClientFactory extends Factory
{
    protected $model = ExportClient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'x509_subject_dn_common_name' => $this->faker->word(),
            'pseudo_id_key_pair' => sodium_crypto_box_seed_keypair(random_bytes(SODIUM_CRYPTO_BOX_SEEDBYTES)),
            'pseudo_id_nonce' => random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES),
        ];
    }
}
