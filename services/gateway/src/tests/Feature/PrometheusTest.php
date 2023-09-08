<?php

declare(strict_types=1);

namespace Tests\Feature;

use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Redis\Connections\PredisConnection;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

final class PrometheusTest extends TestCase
{
    private PrometheusExporter $prometheus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prometheus = $this->app->get(PrometheusExporter::class);
        $this->prometheus->getPrometheus()->wipeStorage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->prometheus->getPrometheus()->wipeStorage();
    }

    public function testCounter(): void
    {
        $counter = $this->prometheus->getOrRegisterCounter('test', 'Test counter');

        $counter->inc();
        $export = $this->prometheus->export();
        $this->assertCount(1, $export);
        $this->assertEquals('gateway_test', $export[0]->getName());
        $this->assertEquals('counter', $export[0]->getType());
        $this->assertCount(1, $export[0]->getSamples());
        $this->assertEquals('1', $export[0]->getSamples()[0]->getValue());

        $counter->inc();
        $export = $this->prometheus->export();
        $this->assertCount(1, $export);
        $this->assertEquals('gateway_test', $export[0]->getName());
        $this->assertEquals('counter', $export[0]->getType());
        $this->assertCount(1, $export[0]->getSamples());
        $this->assertEquals('2', $export[0]->getSamples()[0]->getValue());

        // check if Redis is used
        $redis = Redis::connection('prometheus');
        $this->assertInstanceOf(PredisConnection::class, $redis);

        $counters = $redis->smembers('PROMETHEUS_counter_METRIC_KEYS');
        $this->assertEquals(['gateway_database_PROMETHEUS_:counter:gateway_test'], $counters);
        $this->assertCount(1, $counters);
        $this->assertEquals(1, $redis->exists('PROMETHEUS_:counter:gateway_test'));
    }
}
