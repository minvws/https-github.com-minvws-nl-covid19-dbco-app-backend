<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Normalizer;

use App\Services\SearchHash\Normalizer\NoOpNormalizer;
use App\Services\SearchHash\Normalizer\Normalizer;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function is_callable;

#[Group('search-hash')]
final class NoOpNormalizerTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $normalizer = new NoOpNormalizer();

        $this->assertInstanceOf(NoOpNormalizer::class, $normalizer);
        $this->assertInstanceOf(Normalizer::class, $normalizer);
    }

    public function testItsInvokable(): void
    {
        $this->assertTrue(is_callable(new NoOpNormalizer()), 'Normalizer is not invokable/callable!');
    }

    public function testNormalize(): void
    {
        $normalizer = new NoOpNormalizer();

        $strings = Collection::make([
            'Renée ' => 'Renée ',
            'Noël ' => 'Noël ',
            'Sørina' => 'Sørina',
            'François' => 'François',
            ' Mónica' => ' Mónica',
            'Ruairí' => 'Ruairí',
            'Mátyás' => 'Mátyás',
            ' Jokūbas ' => ' Jokūbas ',
            'Siân' => 'Siân',
            'Agnès ' => 'Agnès ',
            'KŠthe' => 'KŠthe',
            'Øyvind' => 'Øyvind',
            'Asbjørn' => 'Asbjørn',
            'Fañch' => 'Fañch',
            'Tom Holland ' => 'Tom Holland ',
            "\tGert\t\tDe Huis" => "\tGert\t\tDe Huis",
            'Mary-Jo' => 'Mary-Jo',
            '+06 12345678' => '+06 12345678',
            '2010-01-01' => '2010-01-01',
        ]);

        $this->assertSame($strings->values()->toArray(), $normalizer->normalize($strings->keys())->toArray());
    }
}
