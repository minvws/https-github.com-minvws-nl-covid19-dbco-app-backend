<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Assignment\AssignmentJwtTokenAuthService;
use App\Services\Assignment\AssignmentJwtTokenService;
use App\Services\Assignment\AssignmentTokenAuthService;
use App\Services\Assignment\AssignmentTokenService;
use App\Services\Assignment\TokenDecoder\FirebaseHs256TokenDecoder;
use App\Services\Assignment\TokenDecoder\TokenDecoder;
use App\Services\Assignment\TokenEncoder\FirebaseHs256TokenEncoder;
use App\Services\Assignment\TokenEncoder\TokenEncoder;
use App\Services\Assignment\TokenFetcher\RequestHeaderTokenFetcher;
use App\Services\Assignment\TokenFetcher\TokenFetcher;
use App\Services\Assignment\TokenProcessor\JwtTokenProcessor;
use App\Services\Assignment\TokenProcessor\TokenProcessor;
use Illuminate\Support\ServiceProvider;

final class AssignmentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerTokenFetcher();
        $this->registerTokenEncoder();
        $this->registerTokenDecoder();
        $this->registerTokenProcessor();
        $this->registerAssignmentTokenService();
        $this->registerAssignmentTokenAuthService();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }

    private function registerTokenFetcher(): void
    {
        $this->app->bind(TokenFetcher::class, RequestHeaderTokenFetcher::class);
    }

    private function registerTokenEncoder(): void
    {
        $this->app->bind(TokenEncoder::class, FirebaseHs256TokenEncoder::class);
    }

    private function registerTokenDecoder(): void
    {
        $this->app->bind(TokenDecoder::class, FirebaseHs256TokenDecoder::class);
    }

    private function registerTokenProcessor(): void
    {
        $this->app->bind(TokenProcessor::class, JwtTokenProcessor::class);
    }

    private function registerAssignmentTokenService(): void
    {
        $this->app->bind(AssignmentTokenService::class, AssignmentJwtTokenService::class);
    }

    private function registerAssignmentTokenAuthService(): void
    {
        $this->app->bind(AssignmentTokenAuthService::class, AssignmentJwtTokenAuthService::class);
    }
}
