<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;
use App\Repositories\SectionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function array_filter;
use function array_map;
use function trim;

class SectionService
{
    private PlaceService $placeService;
    private SectionRepository $sectionRepository;

    public function __construct(
        PlaceService $placeService,
        SectionRepository $sectionRepository,
    ) {
        $this->placeService = $placeService;
        $this->sectionRepository = $sectionRepository;
    }

    public function createSectionsWithContext(array $sections, ?Context $context, Place $place): array
    {
        DB::transaction(function () use ($context, $sections, $place, &$sectionList): void {
            $sectionList = array_map(function ($section) use ($context, $place): array {
                // if uuid has been set & can be found within the database
                if (isset($section['uuid'])) {
                    Log::warning('Skipping creating section: uuid found');
                    return [];
                }

                $eloquentSection = $this->placeService->addSectionToPlace($this->formatLabel($section['label']), $place, $context ?? null);

                return $this->buildSectionArray($eloquentSection);
            }, $sections);
        });

        return $this->cleanResults($sectionList ?? []);
    }

    /**
     * @param array $sections
     *
     * @return array
     */
    public function updateSectionsWithContext(array $sections): array
    {
        DB::transaction(function () use ($sections, &$sectionList): void {
            $sectionList = array_map(function ($section): array {
                if (!isset($section['uuid'])) {
                    Log::warning('Skipping updating section: has no uuid');
                    return [];
                }

                $eloquentSection = $this->sectionRepository->getSectionByUuid($section['uuid']);

                if ($eloquentSection === null) {
                    Log::warning('Skipping section, not found within database');
                    return [];
                }

                $eloquentSection->label = $section['label'];
                $eloquentSection->save();

                return $this->buildSectionArray($eloquentSection);
            }, $sections);
        });

        return $this->cleanResults($sectionList ?? []);
    }

    /**
     * @param array<string> $mergeSectionUuids
     */
    public function mergeSectionsInSection(Section $mainSection, array $mergeSectionUuids): Section
    {
        // Get all sections
        $mergeSections = $this->sectionRepository->getSectionsByUuids($mergeSectionUuids);

        foreach ($mergeSections as $mergeSection) {
            // Move every context to the desired section
            foreach ($mergeSection->contexts as $context) {
                $this->sectionRepository->moveContextToSection($context, $mergeSection, $mainSection);
            }

            // Delete the old section
            $mergeSection->delete();
        }

        // Return a fresh instance of the main section
        return $mainSection->refresh();
    }

    private function formatLabel(string $label): string
    {
        return trim($label);
    }

    /**
     * @param array $results
     *
     * @return array
     */
    private function cleanResults(array $results): array
    {
        return array_filter($results);
    }

    /**
     * @return array
     */
    private function buildSectionArray(Section $section): array
    {
        return [
            'uuid' => $section->uuid,
            'label' => $section->label,
            'index_count' => $section->indexCount(),
        ];
    }
}
