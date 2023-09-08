<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;

class DbSectionRepository implements SectionRepository
{
    public function getSectionByPlaceAndLabel(Place $place, string $label): ?Section
    {
        return Section::where('place_uuid', $place->uuid)
            ->where('label', $label)
            ->first();
    }

    public function getSectionsByUuids(array $uuids, array $with = []): array
    {
        $builder = Section::query();
        $builder->whereIn('uuid', $uuids);
        $builder->with($with);
        $result = $builder->get();

        return $result->all();
    }

    public function getSectionByUuid(string $uuid, array $with = []): ?Section
    {
        return Section::where('uuid', $uuid)
            ->with($with)
            ->first();
    }

    public function createSection(Place $place, string $label): Section
    {
        $section = new Section();
        $section->place_uuid = $place->uuid;
        $section->label = $label;
        $section->save();

        return $section;
    }

    public function linkContextToSection(Context $context, Section $section): void
    {
        $section->contexts()->syncWithoutDetaching([$context->uuid]);
    }

    public function unlinkContextFromSection(Context $context, Section $section): void
    {
        $section->contexts()->detach($context->uuid);
    }

    public function moveContextToSection(Context $context, Section $fromSection, Section $toSection): void
    {
        if (!$context->sections->find($toSection)) {
            $this->linkContextToSection($context, $toSection);
        }

        $this->unlinkContextFromSection($context, $fromSection);
    }

    public function unlinkSectionsFromContext(Context $context, bool $deleteDangledSections): void
    {
        foreach ($context->sections as $section) {
            $this->unlinkContextFromSection($context, $section);

            if (!$deleteDangledSections) {
                continue;
            }

            Section::leftJoin(
                'context_section',
                'context_section.section_uuid',
                '=',
                'section.uuid',
            )->whereNull('context_section.section_uuid')->delete();
        }
    }
}
