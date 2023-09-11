<?php

declare(strict_types=1);

namespace App\Models\Intake;

use JsonException;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

use function assert;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class ListOptions implements Decodable
{
    public int $perPage = 20;
    public int $page = 1;
    public ?string $sort = null;
    public ?string $order = null;
    public array $filter = [];
    public bool $includeTotal = false;

    /**
     * @inheritDoc
     *
     * @throws JsonException
     */
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

        if ($container->contains('includeTotal')) {
            $options->includeTotal = $container->get('includeTotal')->decodeString() === '1';
        }

        $options->sort = $container->get('sort')->decodeStringIfPresent();
        $options->order = $container->get('order')->decodeStringIfPresent();

        if ($container->contains('filter')) {
            $decodedFilter = json_decode($container->get('filter')->decodeString(), true, 512, JSON_THROW_ON_ERROR);
            assert(is_array($decodedFilter));
            $options->filter = $decodedFilter;
        }

        return $options;
    }
}
