<?php

declare(strict_types=1);

namespace App\Models\Catalog;

enum Filter: string implements \App\Schema\Filter\Filter
{
    case All = 'all';
    case Main = 'main';

    public function getIdentifier(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::All => 'Alle',
            self::Main => 'Hoofd',
        };
    }
}
