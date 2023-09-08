<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Redis;
use Predis\Client as PredisClient;
use Tests\TestCase;

final class CacheTest extends TestCase
{
    public function testPrometheusCacheConnection(): void
    {
        $client = Redis::connection('prometheus')->client();
        $this->assertInstanceOf(PredisClient::class, $client);

        $client->set('key', 'value');

        $value = $client->get('key');
        $this->assertEquals('value', $value);

        $client->del('key');
        $this->assertNull($client->get('key'));
    }
}
