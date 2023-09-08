<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\ValidateFilters;
use Illuminate\Routing\Router;
use MinVWS\Audit\Services\AuditService;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Feature\Http\Middleware\Stubs\MyBackedEnum;
use Tests\Feature\Http\Middleware\Stubs\MyEnum;
use Tests\Feature\Http\Middleware\Stubs\ValidateFiltersController;

use function array_column;
use function sprintf;

#[Group('policy')]
final class ValidateFiltersTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var AuditService $auditService */
        $auditService = $this->app->make(AuditService::class);
        $auditService->setEventExpected(false);

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router
            ->middleware(ValidateFilters::class)
            ->group(static function (Router $router): void {
                $router->get('/test/closure', static function (): array {
                    return ['hello' => 'world'];
                });

                $router->get('/test/controller', [ValidateFiltersController::class, 'index']);
            });
    }

    public function testUsingItOnClosureEndpointDoesNotCauseAnError(): void
    {
        $this
            ->getJson('/test/closure')
            ->assertOk()
            ->assertJson(['hello' => 'world']);
    }

    public function testItReturnsBadRequestOnMissingRequiredFilter(): void
    {
        $this
            ->getJson('/test/controller')
            ->assertBadRequest()
            ->assertJsonIsObject()
            ->assertJsonValidationErrors([
                'filter.my_filter_one' => 'Query filter parameter "my_filter_one" is required!',
            ]);
    }

    public function testItReturnsBadRequestOnInvalidFilterUsingBackedEnum(): void
    {
        $myFilterOne = $this->faker->word();

        $this
            ->getJson(sprintf('/test/controller?filter[my_filter_one]=%s', $myFilterOne))
            ->assertBadRequest()
            ->assertJsonIsObject()
            ->assertJsonValidationErrors([
                'filter.my_filter_one' => 'Query filter parameter "my_filter_one" is invalid! Allowed values are: "foo", "bar"',
            ]);
    }

    public function testItReturnsBadRequestOnInvalidFilterUsingDbcoEnum(): void
    {
        $myFilterOne = $this->faker->randomElement(array_column(MyBackedEnum::cases(), 'value'));
        $myFilterTwo = $this->faker->word();

        $this
            ->getJson(sprintf('/test/controller?filter[my_filter_one]=%s&filter[my_filter_two]=%s', $myFilterOne, $myFilterTwo))
            ->assertBadRequest()
            ->assertJsonIsObject()
            ->assertJsonValidationErrors([
                'filter.my_filter_two' => 'Query filter parameter "my_filter_two" is invalid! Allowed values are: "foo", "bar"',
            ]);
    }

    public function testItSetsTheFilters(): void
    {
        $myFilterOne = MyBackedEnum::FOO->value;
        $myFilterTwo = MyEnum::bar()->value;

        $this
            ->getJson(sprintf('/test/controller?filter[my_filter_one]=%s&filter[my_filter_two]=%s', $myFilterOne, $myFilterTwo))
            ->assertOk()
            ->assertJsonIsObject()
            ->assertJsonFragment([
                'my_filter_one' => $myFilterOne,
                'my_filter_two' => $myFilterTwo,
                'my_filter_three' => null,
                'my_filter_four' => null,
            ]);
    }
}
