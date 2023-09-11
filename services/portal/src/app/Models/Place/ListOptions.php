<?php

declare(strict_types=1);

namespace App\Models\Place;

use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;
use MinVWS\DBCO\Enum\Models\ContextCategoryGroup;
use MinVWS\DBCO\Enum\Models\ContextListView;

use function filter_var;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

class ListOptions implements Decodable
{
    public int $perPage = 20;
    public int $page = 1;
    public ?ContextListView $view = null;
    public ?ContextCategoryGroup $categoryGroup = null;
    public ?bool $isVerified = null;
    public ?string $sort = null;
    public ?string $order = null;

    /**
     * @inheritDoc
     */
    public static function decode(DecodingContainer $container, ?object $object = null)
    {
        $options = $object ?? new self();

        if ($container->contains('perPage')) {
            $options->perPage = (int) $container->perPage->decode();
        }

        if ($container->contains('page')) {
            $options->page = (int) $container->page->decode();
        }

        if ($container->contains('view')) {
            /** @var ContextListView $decodedView */
            $decodedView = $container->view->decodeObject(ContextListView::class);
            $options->view = $decodedView;
        }

        if ($container->contains('categoryGroup')) {
            /** @var ContextCategoryGroup $decodedCategoryGroup */
            $decodedCategoryGroup = $container->categoryGroup->decodeObject(ContextCategoryGroup::class);
            $options->categoryGroup = $decodedCategoryGroup;
        }

        if ($container->contains('isVerified')) {
            $options->isVerified = filter_var($container->isVerified->decode(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if ($container->contains('sort')) {
            $options->sort = $container->sort->decode();
        }

        if ($container->contains('order')) {
            $options->order = $container->order->decode();
        }

        return $options;
    }
}
