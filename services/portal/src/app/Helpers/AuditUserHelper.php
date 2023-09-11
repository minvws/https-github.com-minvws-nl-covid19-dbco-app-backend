<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Export\ExportClient;
use App\Schema\Purpose\Purpose;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use MinVWS\Audit\Models\AuditUser;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_map;
use function assert;
use function request;

class AuditUserHelper
{
    public static function getAuditUser(): AuditUser
    {
        if (Auth::check()) {
            $user = Auth::user();
            Assert::notNull($user);
            return self::getAuthenticatedAuditUser($user);
        }

        if (App::runningInConsole()) {
            return AuditUser::create('portal', 'console');
        }

        return AuditUser::create('portal', 'unknown');
    }

    public static function getAuthenticatedAuditUser(Authenticatable $user): AuditUser
    {
        if ($user instanceof EloquentUser) {
            return self::getAuthenticatedAuditUserForPortalUser($user);
        }

        // ExportClient
        assert($user instanceof ExportClient);
        return self::getAuthenticatedAuditUserForExportClient($user);
    }

    private static function getAuthenticatedAuditUserForPortalUser(EloquentUser $user): AuditUser
    {
        /** @var array<EloquentOrganisation> $organisations */
        $organisations = $user->organisations->all();
        $roles = Config::array('authorization.roles');

        return AuditUser::create('portal', $user->external_id)
            ->name($user->name)
            ->detail('organisationNames', array_map(static fn(EloquentOrganisation $o) => $o->name, $organisations))
            ->detail('organisationIds', array_map(static fn(EloquentOrganisation $o) => $o->external_id, $organisations))
            ->ip(request()->ip())
            ->roles(array_map(static function ($role) use ($roles) {
                if (!array_key_exists($role, $roles)) {
                    return $role;
                }

                return $roles[$role];
            }, $user->getRolesArray()));
    }

    private static function getAuthenticatedAuditUserForExportClient(ExportClient $client): AuditUser
    {
        return AuditUser::create('export', $client->x509_subject_dn_common_name)
            ->name($client->name)
            ->detail('organisationNames', array_map(static fn(EloquentOrganisation $o) => $o->name, $client->organisations->all()))
            ->detail('organisationIds', array_map(static fn(EloquentOrganisation $o) => $o->external_id, $client->organisations->all()))
            ->ip(request()->ip())
            ->purposes(array_map(static fn (Purpose $p) => $p->getIdentifier(), $client->getPurposeLimitation()->getPurposes()));
    }
}
