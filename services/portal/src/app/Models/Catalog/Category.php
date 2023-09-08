<?php

declare(strict_types=1);

namespace App\Models\Catalog;

enum Category: string
{
    case Model = 'model';
    case Fragment = 'fragment';
    case Entity = 'entity';
    case Enum = 'enum';
}
