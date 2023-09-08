<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\AuditUserHelper;
use App\Helpers\Config;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use MinVWS\Audit\Models\AuditUser;
use MinVWS\Audit\Services\AuditService;

final class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->extend(AuditService::class, function (AuditService $auditService) {
            $appType = Config::string('app.type');

            $auditService->setService($appType);

            $auditService->setUserCallback(function () use ($appType): AuditUser {
                if ($appType === 'portal') {
                    $auditUser = AuditUserHelper::getAuditUser();
                    $this->handleBcosyncDetail($auditUser);

                    return $auditUser;
                }

                $auditUser = AuditUser::create($appType, 'system');
                $this->handleBcosyncDetail($auditUser);

                return $auditUser;
            });

            return $auditService;
        });
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleBcosyncDetail(AuditUser $auditUser): void
    {
        $request = $this->app->make('request');
        if (!$request instanceof Request) {
            return;
        }

        if ($request->hasHeader('bcosync-version') || $request->hasHeader('X-bcosync-version')) {
            $auditUser->detail('bcosync', true);
        }
    }
}
