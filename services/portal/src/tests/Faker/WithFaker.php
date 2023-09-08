<?php

declare(strict_types=1);

namespace Tests\Faker;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

use function config;
use function is_null;

trait WithFaker
{
    protected Generator $faker;
    private static ?Generator $staticFaker = null;

    /**
     * Setup up the Faker instance.
     */
    protected function setUpFaker(): void
    {
        $this->faker = FakerFactory::addProviders($this->makeFaker());
    }

    /**
     * Get the default Faker instance for a given locale.
     */
    protected function faker(?string $locale = null): Generator
    {
        return is_null($locale) ? $this->faker : $this->makeFaker($locale);
    }

    /**
     * Create a Faker instance for the given locale.
     */
    protected function makeFaker(?string $locale = null): Generator
    {
        $locale ??= config('app.faker_locale', Factory::DEFAULT_LOCALE);

        if (isset($this->app) && $this->app->bound(Generator::class)) {
            return $this->app->make(Generator::class, ['locale' => $locale]);
        }

        return Factory::create($locale);
    }

    public static function getFaker(): Generator
    {
        if (isset(self::$staticFaker)) {
            return self::$staticFaker;
        }

        return self::$staticFaker = FakerFactory::addProviders(Factory::create('nl_NL'));
    }

    #[Before]
    protected function setupFakerForUnitTestCase(): void
    {
        $this->faker = self::getFaker();
    }

    #[After]
    protected function teardownFakerForUnitTestCase(): void
    {
        self::$staticFaker = null;
    }
}
