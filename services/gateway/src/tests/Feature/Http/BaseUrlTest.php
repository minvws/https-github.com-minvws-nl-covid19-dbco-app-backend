<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Tests\TestCase;

final class BaseUrlTest extends TestCase
{
    public function testThatBaseEndpointRedirectsToVersionPage(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/version');
    }
}
