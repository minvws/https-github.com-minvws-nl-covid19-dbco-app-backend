<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Normalizer;

use App\Services\SearchHash\Normalizer\HashNormalizer;
use App\Services\SearchHash\Normalizer\Normalizer;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function is_callable;

#[Group('search-hash')]
final class HashNormalizerTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $normalizer = new HashNormalizer();

        $this->assertInstanceOf(HashNormalizer::class, $normalizer);
        $this->assertInstanceOf(Normalizer::class, $normalizer);
    }

    public function testItsInvokable(): void
    {
        $this->assertTrue(is_callable(new HashNormalizer()), 'Normalizer is not invokable/callable!');
    }

    public function testNormalize(): void
    {
        $this->assertSame(
            $this->getData()->values()->toArray(),
            (new HashNormalizer())->normalize($this->getData()->keys())->toArray(),
        );
    }

    public function testInvokable(): void
    {
        $this->assertSame(
            $this->getData()->values()->toArray(),
            (new HashNormalizer())($this->getData()->keys())->toArray(),
        );
    }

    public function testNormalizeString(): void
    {
        $normalizer = new HashNormalizer();

        foreach ($this->getData() as $input => $expected) {
            $this->assertSame($expected, $normalizer->normalizeString($input));
        }
    }

    /**
     * @return Collection<string,string>
     */
    private function getData(): Collection
    {
        return Collection::make([
            'Renée ' => 'renee',
            'Noël ' => 'noel',
            'Sørina' => 'sorina',
            'François' => 'francois',
            ' Mónica' => 'monica',
            'Ruairí' => 'ruairi',
            'Mátyás' => 'matyas',
            ' Jokūbas ' => 'jokubas',
            'Siân' => 'sian',
            'Agnès ' => 'agnes',
            'KŠthe' => 'ksthe',
            'Øyvind' => 'oyvind',
            'Asbjørn' => 'asbjorn',
            'Fañch' => 'fanch',
            'Tom Holland ' => 'tomholland',
            "\tGert\t\tDe Huis" => 'gertdehuis',
            'Mary-Jo' => 'maryjo',
            '+06 12345678' => '+0612345678',
            '2010-01-01' => '20100101',
            '*****007' => '007',
        ]);
    }
}
