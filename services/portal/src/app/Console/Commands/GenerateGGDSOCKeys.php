<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\GgdSocCrypter;
use Illuminate\Console\Command;

class GenerateGGDSOCKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ggdsockeys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create public and secret keys for audit log exchange with GGD SOC';

    public function handle(): int
    {
        $base64keys = GgdSocCrypter::generateBase64KeySet();
        $this->info("Public (URL-safe Base64 encoding, without = padding characters)");
        $this->info($base64keys['public']);
        $this->info("Secret");
        $this->info($base64keys['secret']);
        return 0;
    }
}
