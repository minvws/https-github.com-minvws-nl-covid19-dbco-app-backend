<?php

declare(strict_types=1);

namespace Tests\Feature\OpenAPI;

use App\Events\OpenAPI\OpenAPIValidationFailedEvent;
use App\Http\Middleware\ValidateOpenAPISpec;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tests\Feature\FeatureTestCase;
use Throwable;

use function app;
use function config;
use function url;

class ValidateOpenAPISpecTest extends FeatureTestCase
{
    private readonly ValidateOpenAPISpec $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = app(ValidateOpenAPISpec::class);
    }

    /**
     * @throws Exception|Throwable
     */
    public function testItLoadsTheOpenApiSpecFileCorrectly(): void
    {
        $request = Request::create(url('api/cases/assignment/options'), 'post');

        $response = $this->middleware->handle($request, static function () {
            return new JsonResponse([
                'options' => [],
            ]);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testItThrowsANoPathExceptionWhenNoMatchingPathInSpec(): void
    {
        $mock = $this->partialMock(ValidatorBuilder::class);
        $mock->allows('validate')
            ->andThrow(new NoPath('No matching path'));

        $request = Request::create(url('fake/request/'), 'post');

        Event::fake();
        $this->middleware->handle($request, static function () {
            return new JsonResponse([
                'options' => [],
            ]);
        });
        Event::assertNotDispatched(OpenAPIValidationFailedEvent::class);
    }

    /**
     * @throws Throwable
     */
    public function testItDispatchesAValidationFailedEventWhenSpecIsIncorrect(): void
    {
        $request = Request::create(url('api/cases/assignment/options'), 'post');

        Event::fake();
        $this->middleware->handle($request, static function () {
            return new JsonResponse();
        });
        Event::assertDispatched(OpenAPIValidationFailedEvent::class);
    }

    public function testItThrowsAnExceptionWhenTheThrowsExceptionConfigValueIsTrue(): void
    {
        config(['openapi.throw_exceptions' => true]);
        $request = Request::create(url('api/cases/assignment/options'), 'post');
        //Manual instantiation of the middleware to load the new mocked config value
        $middleware = app(ValidateOpenAPISpec::class);

        $this->expectException(Throwable::class);
        $middleware->handle($request, static function () {
            return new JsonResponse();
        });
    }

    public function testItSkipsProductionEnvironments(): void
    {
        app()['env'] = 'production';
        $mock = $this->mock(ValidatorBuilder::class);
        $mock->shouldNotReceive('fromYamlFile');

        $request = Request::create(url('api/cases/assignment/options'), 'post');

        $this->middleware->handle($request, static function () {
            return new JsonResponse();
        });

        $this->asserttrue(true);
        app()['env'] = 'testing';
    }
}
