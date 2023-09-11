<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Catalog\Index;
use App\Models\Catalog\Options;
use App\Schema\Types\Type;
use App\Services\Catalog\TypeRepository;

class CatalogService
{
    private TypeRepository $typeRepository;

    public function __construct(TypeRepository $elementRepository)
    {
        $this->typeRepository = $elementRepository;
    }

    public function getIndex(Options $options): Index
    {
        return new Index($options, $this->typeRepository->getTypes($options));
    }

    public function getType(string $class, ?int $version = null): ?Type
    {
        return $this->typeRepository->getType($class, $version);
    }
}
