<?php

declare(strict_types=1);

namespace App\Models\CaseList;

use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

class ListOptions implements Decodable
{
    public const TYPE_QUEUE = 'queue';
    public const TYPE_LIST = 'list';

    public int $perPage = 20;
    public int $page = 1;
    public bool $stats = false;
    public array $types = [
        self::TYPE_QUEUE,
        self::TYPE_LIST,
    ];

    public static function decode(DecodingContainer $container, ?object $object = null): ListOptions
    {
        /** @var ListOptions $options */
        $options = $object ?? new self();

        if ($container->contains('perPage')) {
            $options->perPage = (int) $container->get('perPage')->decodeString();
        }

        if ($container->contains('page')) {
            $options->page = (int) $container->get('page')->decodeString();
        }

        $options->stats = $container->get('stats')->decodeStringIfPresent() === '1';

        if ($container->contains('types')) {
            $options->types = $container->get('types')->decodeArray('string');
        }

        return $options;
    }

    public function onlyQueues(): bool
    {
        return $this->types === [self::TYPE_QUEUE];
    }

    public function onlyLists(): bool
    {
        return $this->types === [self::TYPE_LIST];
    }
}
