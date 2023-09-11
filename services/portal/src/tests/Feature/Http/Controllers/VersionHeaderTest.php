<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function env;

#[Group('version-header')]
class VersionHeaderTest extends FeatureTestCase
{
    public function testCheckIfWeAlwaysReturnAVersionHeader(): void
    {
        $response = $this->get('/does-not-exist');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertHeader('X-GGD-Contact-Version', env('APP_VERSION', 'latest'));

        $response = $this->get('/api/does-not-exist');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertHeader('X-GGD-Contact-Version', env('APP_VERSION', 'latest'));
    }
}
