<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\SearchHash\Hasher\Hasher;
use App\Services\SearchHash\Hasher\Pbkdf2Hasher;
use App\Services\SearchHash\Normalizer\HashNormalizer;
use App\Services\SearchHash\Normalizer\Normalizer;
use Illuminate\Support\ServiceProvider;

class SearchHashServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(Hasher::class, Pbkdf2Hasher::class);
        $this->app->bind(Normalizer::class, HashNormalizer::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
