<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class PrometheusRouteMiddlewareTest extends TestCase
{
    private PrometheusExporter $prometheusExporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prometheusExporter = $this->app->get(PrometheusExporter::class);
        $this->prometheusExporter->getPrometheus()->wipeStorage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->prometheusExporter->getPrometheus()->wipeStorage();
    }

    public function testPrometheusRouteMiddleware(): void
    {
        $this->refreshApplication();

        Route::group(
            ['middleware' => 'prometheus'],
            static function (): void {
                Route::get('foo', fn() => 'bar');
            },
        );

        $export = $this->prometheusExporter->export();
        $this->assertCount(0, $export);

        $response = $this->call('GET', 'foo');
        $this->assertEquals(200, $response->status());

        $export = $this->prometheusExporter->export();
        $this->assertCount(2, $export);
    }
}
