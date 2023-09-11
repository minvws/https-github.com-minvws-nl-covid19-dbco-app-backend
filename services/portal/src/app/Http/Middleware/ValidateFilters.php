<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Api\Admin\Attributes\RequestHasFixedValuesQueryFilter;
use App\Http\Controllers\Api\Admin\Attributes\RequestQueryFilter;
use App\Http\Controllers\Controller;
use BackedEnum;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\Enum;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;
use ValueError;
use Webmozart\Assert\Assert;

use function array_column;
use function array_map;
use function explode;
use function implode;
use function is_a;
use function is_null;
use function sprintf;

class ValidateFilters
{
    /** @var Collection<Enum|BackedEnum> */
    private Collection $enumsByQueryKey;

    public function __construct(protected readonly Application $app)
    {
        $this->enumsByQueryKey = new Collection();
    }

    /**
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        Assert::isInstanceOf($route, Route::class);

        $actionName = $route->getActionName();
        if ($actionName === 'Closure') {
            return $next($request);
        }

        $this->handleNonClosureRoute($request, $route, $actionName);

        $this->clearEnums();

        return $next($request);
    }

    public function handleNonClosureRoute(Request $request, Route $route, string $actionName): void
    {
        /** @var class-string<Controller> $class */
        [$class, $method] = explode('@', $actionName, 2);

        $attributes = (new ReflectionMethod($class, $method))->getAttributes(RequestHasFixedValuesQueryFilter::class);

        foreach ($attributes as $attribute) {
            /** @var RequestHasFixedValuesQueryFilter $attributeInstance */
            $attributeInstance = $attribute->newInstance();

            $queryValue = $request->query('filter', [])[$attributeInstance->name] ?? null;

            $this->validateQueryExistence($attributeInstance, $queryValue);
            $this->getEnumInstanceOrThrowValidationException($attributeInstance, $queryValue);
            $this->setQueryFilterValues($route, $class);
        }
    }

    private function validateQueryExistence(RequestHasFixedValuesQueryFilter $attributeInstance, ?string $queryValue): void
    {
        if ($attributeInstance->required && $queryValue === null) {
            throw ValidationException::withMessages([
                sprintf('filter.%s', $attributeInstance->name) => sprintf(
                    'Query filter parameter "%s" is required!',
                    $attributeInstance->name,
                ),
            ])->status(Response::HTTP_BAD_REQUEST);
        }
    }

    private function getEnumInstanceOrThrowValidationException(RequestHasFixedValuesQueryFilter $attributeInstance, ?string $queryValue): void
    {
        if (is_null($queryValue)) {
            $this->enumsByQueryKey->put($attributeInstance->name, null);
            return;
        }

        try {
            $this->enumsByQueryKey->put($attributeInstance->name, $attributeInstance->enumClass::from($queryValue));
        } catch (ValueError | InvalidArgumentException) {
            if (is_a($attributeInstance->enumClass, Enum::class, true)) {
                $values = $attributeInstance->enumClass::allValues();
            } elseif (is_a($attributeInstance->enumClass, BackedEnum::class, true)) {
                $values = array_column($attributeInstance->enumClass::cases(), 'value');
            }
            $values = array_map(static fn (string $v): string => sprintf('"%s"', $v), $values ?? []);
            $values = implode(', ', $values);

            throw ValidationException::withMessages([
                sprintf('filter.%s', $attributeInstance->name) => sprintf(
                    'Query filter parameter "%s" is invalid! Allowed values are: %s.',
                    $attributeInstance->name,
                    $values,
                ),
            ])->status(Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param class-string<Controller> $class
     */
    private function setQueryFilterValues(Route $route, string $class): void
    {
        $properties = (new ReflectionClass($class))->getProperties();

        /** @var Controller $controller */
        $controller = $route->getController();

        foreach ($properties as $property) {
            $attributes = $property->getAttributes(RequestQueryFilter::class);

            foreach ($attributes as $attribute) {
                /** @var RequestQueryFilter $attributeInstance */
                $attributeInstance = $attribute->newInstance();

                $enumInstance = $this->enumsByQueryKey->get($attributeInstance->name);

                if (is_null($enumInstance)) {
                    continue;
                }

                $property->setValue($controller, $enumInstance);
            }
        }
    }

    private function clearEnums(): void
    {
        $this->enumsByQueryKey = new Collection();
    }
}
