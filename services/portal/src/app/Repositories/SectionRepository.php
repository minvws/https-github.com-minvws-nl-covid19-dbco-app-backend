<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;

interface SectionRepository
{
    public function getSectionByPlaceAndLabel(Place $place, string $label): ?Section;

    public function getSectionsByUuids(array $uuids, array $with = []): array;

    public function getSectionByUuid(string $uuid, array $with = []): ?Section;

    public function createSection(Place $place, string $label): Section;

    public function linkContextToSection(Context $context, Section $section): void;

    public function unlinkContextFromSection(Context $context, Section $section): void;

    public function moveContextToSection(Context $context, Section $fromSection, Section $toSection): void;

    /**
     * @param bool $deleteDangledSections If true, this will remove the sections that are
     *                                    not linked to any other context
     */
    public function unlinkSectionsFromContext(Context $context, bool $deleteDangledSections): void;
}
