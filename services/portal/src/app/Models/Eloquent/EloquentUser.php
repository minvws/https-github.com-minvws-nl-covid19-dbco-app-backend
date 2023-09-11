<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Helpers\Config;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\User\UserCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use App\Scopes\OrganisationAuthScope;
use App\Services\Assignment\AssignmentTokenable;
use App\Services\Assignment\HasAssignmentToken;
use App\Services\AuthorizationService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use RuntimeException;

use function app;
use function explode;
use function in_array;
use function sprintf;

/**
 * @property string $uuid
 * @property string $name
 * @property string $external_id
 * @property ?string $roles
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?CarbonImmutable $consented_at
 * @property ?CarbonImmutable $last_login_at
 *
 * @property Collection<int, EloquentOrganisation> $organisations
 */
class EloquentUser extends EloquentBaseModel implements
    Authenticatable,
    AuthorizableInterface,
    SchemaObject,
    SchemaProvider,
    UserCommon,
    HasAssignmentToken,
    BelongsToOrganisation
{
    use HasSchema;
    use HasFactory;
    use CamelCaseAttributes;
    use Authorizable;
    use AssignmentTokenable;

    protected $table = 'bcouser';
    protected $fillable = [
        'uuid',
        'name',
        'roles',
        'external_id',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setName('User');
        $schema->setVersionedNamespace('App\\Models\\Versions\\User');
        $schema->setCurrentVersion(1);

        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(StringType::createField('name'))->setAllowsNull(false);
        $schema->add(StringType::createField('roles'));
        $schema->add(EloquentOrganisation::getSchema()->getVersion(1)->createArrayField('organisations'))->setAllowsNull(false);


        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function organisations(): BelongsToMany
    {
        return
            $this
            ->belongsToMany(EloquentOrganisation::class, 'user_organisation', 'user_uuid', 'organisation_uuid')
            ->withoutGlobalScope(OrganisationAuthScope::class)
            ->withTimestamps();
    }

    public function getAuthIdentifierName(): string
    {
        return 'uuid';
    }

    public function getAuthIdentifier(): string
    {
        return $this->uuid;
    }

    public function getAuthPassword(): string
    {
        return 'not used since we only do auth via oauth';
    }

    public function getRememberToken(): string
    {
        return 'not used either';
    }

    /**
     * @inheritdoc
     */
    public function setRememberToken($value): void
    {
        // nothing to do. not implemented
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    private array $permissionCache = [];

    /**
     * Check if the authenticated user has the given permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!isset($this->permissionCache[$permission])) {
            $this->permissionCache[$permission] = $this->hasPermissionUncached($permission);
        }

        return $this->permissionCache[$permission];
    }

    private function hasPermissionUncached(string $permission): bool
    {
        /** @var AuthorizationService $authorizationService */
        $authorizationService = app(AuthorizationService::class);

        $userRoles = $this->getRolesArray();

        return $authorizationService->hasPermission($userRoles, $permission);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRolesArray(), true);
    }

    public function getRolesArray(): array
    {
        if ($this->roles === null) {
            return [];
        }

        return explode(',', $this->roles);
    }

    public function isInOrganisation(string $uuid): bool
    {
        foreach ($this->organisations as $organisation) {
            if ($organisation->uuid === $uuid) {
                return true;
            }
        }

        return false;
    }

    public function getOrganisation(): ?EloquentOrganisation
    {
        /** @var ?EloquentOrganisation */
        return $this->organisations[0] ?? null;
    }

    public function getRequiredOrganisation(): EloquentOrganisation
    {
        $organisation = $this->getOrganisation();
        if ($organisation === null) {
            throw new RuntimeException(sprintf('User %s must have an organisation', $this->uuid));
        }

        return $organisation;
    }

    public function hasRecentlyLoggedIn(): bool
    {
        $lastLoginThressholdNeededForCaseAssignment = CarbonImmutable::now()
            ->subDays(Config::integer('misc.case.assignment.lastLoginThresholdNeededForCaseAssignmentInDays'));

        return $this->last_login_at > $lastLoginThressholdNeededForCaseAssignment;
    }
}
