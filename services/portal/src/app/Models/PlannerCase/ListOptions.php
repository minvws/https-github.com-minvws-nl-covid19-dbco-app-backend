<?php

declare(strict_types=1);

namespace App\Models\PlannerCase;

use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\TestResultSource;

use function property_exists;

class ListOptions implements Decodable
{
    public int $perPage = 20;
    public int $page = 1;
    public PlannerView $view;
    public ?string $caseListUuid = null;
    public ?string $label = null;
    public ?string $organisation = null;
    public ?string $userAssignment = null;
    public ?ContactTracingStatus $statusIndexContactTracing = null;
    public ?TestResultSource $testResultSource = null;
    public ?PlannerSort $sort = null;
    public ?string $order = null;
    public bool $includeTotal = false;
    public ?int $minAge = null;
    public ?int $maxAge = null;

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
            $options->view = $container->view->decodeObject(PlannerView::class);
        }

        if ($container->contains('caseListUuid')) {
            $options->caseListUuid = $container->caseListUuid->decodeStringIfPresent();
        }

        if ($container->contains('sort')) {
            $options->sort = $container->sort->decodeObject(PlannerSort::class);
        }

        if ($container->contains('order')) {
            $options->order = $container->order->decode();
        }

        if ($container->contains('includeTotal')) {
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator -- might be a string
            $options->includeTotal = $container->includeTotal->decode() == 1;
        }

        if ($container->contains('label')) {
            $options->label = $container->label->decodeStringIfPresent();
        }

        if ($container->contains('organisation')) {
            $options->organisation = $container->organisation->decodeStringIfPresent();
        }

        if ($container->contains('userAssignment')) {
            $options->userAssignment = $container->userAssignment->decodeStringIfPresent();
        }

        if (property_exists($options, 'statusIndexContactTracing') && $container->contains('statusIndexContactTracing')) {
            $options->statusIndexContactTracing = $container->statusIndexContactTracing->decodeObject(ContactTracingStatus::class);
        }

        if (property_exists($options, 'testResultSource') && $container->contains('testResultSource')) {
            $options->testResultSource = $container->testResultSource->decodeObject(TestResultSource::class);
        }

        if (property_exists($options, 'minAge') && $container->contains('minAge')) {
            $options->minAge = (int) $container->minAge->decode();
        }

        if (property_exists($options, 'maxAge') && $container->contains('maxAge')) {
            $options->maxAge = (int) $container->maxAge->decode();
        }

        return $options;
    }
}
