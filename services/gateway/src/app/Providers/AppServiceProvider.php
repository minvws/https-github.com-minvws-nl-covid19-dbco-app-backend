<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\EncryptionService;
use App\Services\JwtTokenService;
use App\Utils\Config;
use Illuminate\Support\ServiceProvider;
use MinVWS\Audit\Services\AuditService;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(AuditService::class, static function (AuditService $auditService): AuditService {
            $auditService->setService(Config::string('app.name'));

            return $auditService;
        });
    }

    public function boot(): void
    {
        $this->app->when(JwtTokenService::class)
            ->needs('$jwtSecret')
            ->giveConfig('services.jwt.secret');

        $this->app->when(EncryptionService::class)
            ->needs('$publicKey')
            ->giveConfig('services.encryption.public_key');
    }
}
