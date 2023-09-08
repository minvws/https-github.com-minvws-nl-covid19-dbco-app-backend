<?php

declare(strict_types=1);

namespace Tests\Feature\Schema\Documentation;

use App\Schema\Documentation\LaravelDocumentationProvider;
use Illuminate\Contracts\Translation\Translator;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

class LaravelDocumentationProviderTest extends FeatureTestCase
{
    protected Translator&MockInterface $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->instance('translator', Mockery::mock(Translator::class));
    }

    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(LaravelDocumentationProvider::class, $this->app->make(LaravelDocumentationProvider::class));
    }

    public function testGetDocumentation(): void
    {
        $this->translator
            ->shouldReceive('get')
            ->with('schema.my_identifer.my_key', [], null)
            ->andReturn('test value');

        /** @var LaravelDocumentationProvider $provider */
        $provider = $this->app->make(LaravelDocumentationProvider::class);

        $this->assertSame('test value', $provider->getDocumentation('my_identifer', 'my_key'));
    }

    public function testGetDocumentationThrowsExceptionOnUnexpectedTranslateResult(): void
    {
        $this->translator
            ->shouldReceive('get')
            ->with('schema.my_identifer.my_key', [], null)
            ->andReturn([]);

        /** @var LaravelDocumentationProvider $provider */
        $provider = $this->app->make(LaravelDocumentationProvider::class);

        $this->expectExceptionObject(new RuntimeException('Unexpected translation result for "schema.my_identifer.my_key"'));

        $provider->getDocumentation('my_identifer', 'my_key');
    }
}
