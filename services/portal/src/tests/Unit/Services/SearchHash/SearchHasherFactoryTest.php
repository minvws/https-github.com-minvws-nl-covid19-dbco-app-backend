<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash;

use App\Services\SearchHash\EloquentCase\Contact\ContactHash;
use App\Services\SearchHash\EloquentCase\Contact\ContactSearchHasher;
use App\Services\SearchHash\EloquentCase\Index\IndexHash;
use App\Services\SearchHash\EloquentCase\Index\IndexSearchHasher;
use App\Services\SearchHash\EloquentTask\General\GeneralHash;
use App\Services\SearchHash\EloquentTask\General\GeneralSearchHasher;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsHash;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsSearchHasher;
use App\Services\SearchHash\SearchHasherFactory;
use Illuminate\Contracts\Foundation\Application;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class SearchHasherFactoryTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var Application&MockInterface $app */
        $app = Mockery::mock(Application::class);

        $this->assertInstanceOf(SearchHasherFactory::class, new SearchHasherFactory($app));
    }

    public function testCovidCaseContact(): void
    {
        /** @var ContactHash&MockInterface $valueObject */
        $valueObject = Mockery::mock(ContactHash::class);

        /** @var ContactSearchHasher&MockInterface $hasher */
        $hasher = Mockery::mock(ContactSearchHasher::class);

        /** @var Application&MockInterface $app */
        $app = Mockery::mock(Application::class);
        $app->expects('make')
            ->with(ContactSearchHasher::class, ['valueObject' => $valueObject])
            ->andReturn($hasher);

        $result = (new SearchHasherFactory($app))->covidCaseContact($valueObject);

        $this->assertInstanceOf(ContactSearchHasher::class, $result);
        $this->assertSame($result, $hasher);
    }

    public function testCovidCaseIndex(): void
    {
        /** @var IndexHash&MockInterface $valueObject */
        $valueObject = Mockery::mock(IndexHash::class);

        /** @var IndexSearchHasher&MockInterface $hasher */
        $hasher = Mockery::mock(IndexSearchHasher::class);

        /** @var Application&MockInterface $app */
        $app = Mockery::mock(Application::class);
        $app->expects('make')
            ->with(IndexSearchHasher::class, ['valueObject' => $valueObject])
            ->andReturn($hasher);

        $result = (new SearchHasherFactory($app))->covidCaseIndex($valueObject);

        $this->assertInstanceOf(IndexSearchHasher::class, $result);
        $this->assertSame($result, $hasher);
    }

    public function testTaskGeneral(): void
    {
        /** @var GeneralHash&MockInterface $valueObject */
        $valueObject = Mockery::mock(GeneralHash::class);

        /** @var GeneralSearchHasher&MockInterface $hasher */
        $hasher = Mockery::mock(GeneralSearchHasher::class);

        /** @var Application&MockInterface $app */
        $app = Mockery::mock(Application::class);
        $app->expects('make')
            ->with(GeneralSearchHasher::class, ['valueObject' => $valueObject])
            ->andReturn($hasher);

        $result = (new SearchHasherFactory($app))->taskGeneral($valueObject);

        $this->assertInstanceOf(GeneralSearchHasher::class, $result);
        $this->assertSame($result, $hasher);
    }

    public function testTaskPersonalDetails(): void
    {
        /** @var PersonalDetailsHash&MockInterface $valueObject */
        $valueObject = Mockery::mock(PersonalDetailsHash::class);

        /** @var PersonalDetailsSearchHasher&MockInterface $hasher */
        $hasher = Mockery::mock(PersonalDetailsSearchHasher::class);

        /** @var Application&MockInterface $app */
        $app = Mockery::mock(Application::class);
        $app->expects('make')
            ->with(PersonalDetailsSearchHasher::class, ['valueObject' => $valueObject])
            ->andReturn($hasher);

        $result = (new SearchHasherFactory($app))->taskPersonalDetails($valueObject);

        $this->assertInstanceOf(PersonalDetailsSearchHasher::class, $result);
        $this->assertSame($result, $hasher);
    }
}
