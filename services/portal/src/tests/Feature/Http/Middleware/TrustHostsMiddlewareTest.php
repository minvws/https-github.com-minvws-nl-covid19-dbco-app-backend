<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\TrustHosts;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

class TrustHostsMiddlewareTest extends FeatureTestCase
{
    public function testHosts(): void
    {
        ConfigHelper::set('security.trustedHosts', ['https://foo', 'bar']);

        $trustedHosts = new TrustHosts($this->app);
        $result = $trustedHosts->hosts();

        $expectedResult = [
            '^(.+\.)?localhost$',
            '^(.+\.)?foo$',
        ];

        $this->assertEquals($expectedResult, $result);
    }
}
