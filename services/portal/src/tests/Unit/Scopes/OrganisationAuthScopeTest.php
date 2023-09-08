<?php

declare(strict_types=1);

namespace Tests\Unit\Scopes;

use App\Models\Export\ExportClient;
use App\Scopes\OrganisationAuthScope;
use App\Services\AuthenticationService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class OrganisationAuthScopeTest extends TestCase
{
    public function testDoesNotFilterForExportClient(): void
    {
        $guard = $this->mock(Guard::class);
        $guard->shouldReceive('user')->andReturn(new ExportClient());

        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('where')->never();

        $scope = new OrganisationAuthScope($this->mock(AuthenticationService::class), $guard);
        $scope->apply($builder, $this->mock(Model::class));
    }
}
