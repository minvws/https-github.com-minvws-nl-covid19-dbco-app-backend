<?php

namespace MinVWS\DBCO\Metrics\Services;

use DBCO\Shared\Application\Helpers\TextCaseConvertor;
use DBCO\Shared\Application\Metrics\Events\EditedEvent;
use DBCO\Shared\Application\Metrics\Events\IdentifiedEvent;
use DBCO\Shared\Application\Metrics\Events\InformedEvent;
use DBCO\Shared\Application\Metrics\Events\InventoriedEvent;
use MinVWS\Metrics\Services\EventService as BaseEventService;

/**
 * Event service.
 *
 * @package MinVWS\Metrics\Services
 */
class EventService extends BaseEventService
{
    private const ESSENTIAL_FIELDS = ['lastname', 'firstname', 'phonenumber', 'email', 'date_of_last_exposure'];

    /**
     * Retrieve task answers
     *
     * @param int $taskUuid
     * @return array
     */
    public function retrieveTaskData(string $taskUuid): array
    {
        return $this->storageRepository->getTaskData($taskUuid);
    }

    /**
     * Register Task related events
     *
     * @param $actor
     * @param $caseUuid
     * @param $taskUuid
     * @param array $oldTaskData
     */
    public function registerTaskMetrics($actor, $caseUuid, $taskUuid, array $oldTaskData = [])
    {
        $currentTaskData = $this->retrieveTaskData($taskUuid);

        //If the array entry for a task is not empty, we check the task for updates
        if (!empty($oldTaskData)) {
            $updatedFields = $this->compareEssentialFields($oldTaskData, $currentTaskData);
            if (count($updatedFields) > 0) {
                $this->registerEvent(new EditedEvent($actor, $caseUuid, $taskUuid, $updatedFields));
            };
        } else {
            //If empty this task is newly created
            $this->registerEvent(new IdentifiedEvent($actor, $caseUuid, $taskUuid));
        }

        $inventoriedFields = $this->retrieveInventoriedContactFields($currentTaskData);
        if (count($inventoriedFields) > 0) {
            $this->registerEvent(new InventoriedEvent($actor, $caseUuid, $taskUuid, $inventoriedFields));
        }

        if ($this->contactWasInformedFirstTime($actor, $oldTaskData, $currentTaskData)) {
            $this->registerEvent(new InformedEvent($actor, $caseUuid, $taskUuid));
        }
    }

    /**
     * Register task metrics for an array of tasks
     *
     * @param $actor
     * @param $caseUuid
     * @param array $oldTasksData
     */
    public function registerTasksMetrics($actor, $caseUuid, array $oldTasksData)
    {
        foreach ($oldTasksData as $taskUuid => $oldTaskData) {
            $this->registerTaskMetrics($actor, $caseUuid, $taskUuid, $oldTaskData);
        }
    }


    /**
     * Compare fields for changes and return field names
     *
     * @param $oldTaskData
     * @param $updatedTaskData
     * @return array|string[]
     */
    private function compareEssentialFields(array $oldTaskData, array $updatedTaskData): array
    {
        $updatedQuestionaireFields = $this->compareEssentialQuestionaireFields($oldTaskData, $updatedTaskData);
        $updatedBaseFields = $this->compareEssentialBaseFields($oldTaskData, $updatedTaskData);
        $updatedGeneralFragmentFields = $this->compareEssentialGeneralFragmentFields($oldTaskData, $updatedTaskData);

        return array_merge($updatedQuestionaireFields, $updatedBaseFields, $updatedGeneralFragmentFields);
    }
    /**
     * Compare Contactdetail fields from questionair for changes and return field names
     *
     * @param $oldTaskData
     * @param $updatedTaskData
     * @return array|string[]
     */
    private function compareEssentialQuestionaireFields(array $oldTaskData, array $updatedTaskData): array
    {
        $essentialFields = array_map(fn($field) => 'ctd_' . $field, self::ESSENTIAL_FIELDS);
        $essentialFields = array_fill_keys($essentialFields, null);
        //We only compare essential fields
        $oldTaskData = array_intersect_key($oldTaskData, $essentialFields);
        $updatedTaskData = array_intersect_key($updatedTaskData, $essentialFields);

        return array_keys(array_diff_assoc($updatedTaskData, $oldTaskData));
    }

    /**
     * Compare essential fields from base task for changes and return field names
     *
     * @param $oldTaskData
     * @param $updatedTaskData
     * @return array|string[]
     */
    private function compareEssentialBaseFields(array $oldTaskData, array $updatedTaskData): array
    {
        $essentialFields = array_fill_keys(self::ESSENTIAL_FIELDS, null);

        //We only compare essential fields
        $oldTaskData = array_intersect_key($oldTaskData, $essentialFields);
        $updatedTaskData = array_intersect_key($updatedTaskData, $essentialFields);

        $updatedFields = array_keys(array_diff_assoc($updatedTaskData, $oldTaskData));

        return array_map(fn($f) => TextCaseConvertor::camelToSnake($f), $updatedFields);
    }

    /**
     * Compare essential fields from general fragment for changes and return field names
     *
     * @param $oldTaskData
     * @param $updatedTaskData
     * @return array|string[]
     */
    private function compareEssentialGeneralFragmentFields(array $oldTaskData, array $updatedTaskData): array
    {
        $essentialFields = $this->convertEssentialFieldsToCamelCase();
        //We only compare essential fields
        $oldTaskData = array_intersect_key($oldTaskData['general'] ?? [], $essentialFields);
        $updatedTaskData = array_intersect_key($updatedTaskData['general'] ?? [], $essentialFields);

        $updatedFields = array_keys(array_diff_assoc($updatedTaskData, $oldTaskData));

        return array_map(fn($f) => TextCaseConvertor::camelToSnake($f), $updatedFields);
    }


    /**
     * Check if the contact was informed by checking if the informed_by_actor_at
     * was transitioned from empty to not empty.
     *
     * @param $actor
     * @param array $oldTaskData
     * @param array $updatedTaskData
     * @return bool
     */
    private function contactWasInformedFirstTime($actor, array $oldTaskData, array $updatedTaskData): bool
    {
        $informedByFieldName = 'informed_by_' . $actor . '_at';

        return (
            (
                array_key_exists($informedByFieldName, $oldTaskData) &&
                empty($oldTaskData[$informedByFieldName]) &&
                !empty($updatedTaskData[$informedByFieldName])
            )
            ||
            (
                !array_key_exists($informedByFieldName, $oldTaskData) &&
                !empty($updatedTaskData[$informedByFieldName])
            )

        );
    }

    /**
     * Return an array of essential field keys which don't have empty values
     *
     * @param array $currentTaskData
     * @return array
     */
    private function retrieveInventoriedContactFields(array $currentTaskData): array
    {
        $inventoriedQuestionaireFields = $this->retrieveInventoriedQuestinaireContactFields($currentTaskData);
        $inventoriedBaseFields = $this->retrieveInventoriedBaseContactFields($currentTaskData);
        $inventoriedGeneralFragmentFields = $this->retrieveInventoriedGeneralFragmentContactFields($currentTaskData['general'] ?? []);

        return array_merge($inventoriedQuestionaireFields, $inventoriedBaseFields, $inventoriedGeneralFragmentFields);
    }

    /**
     * Retrieve non empty questionaire contact fields
     *
     * @param array $currentTaskData
     * @return array
     */
    private function retrieveInventoriedQuestinaireContactFields(array $currentTaskData): array
    {
        $currentTaskData = array_intersect_key($currentTaskData, self::ESSENTIAL_FIELDS);
        return array_keys(array_filter($currentTaskData, fn ($value) => !empty($value)));
    }

    /**
     * Retrieve non empty contact fields.
     *
     * @param array $currentTaskData
     * @return array
     */
    private function retrieveInventoriedBaseContactFields(array $currentTaskData): array
    {
        $currentTaskData = array_intersect_key($currentTaskData, array_fill_keys(self::ESSENTIAL_FIELDS, null));
        return array_keys(array_filter($currentTaskData, fn ($value) => !empty($value)));
    }

    /**
     * Retrieve non empty questionaire contact fields
     *
     * @param array $currentTaskData
     * @return array
     */
    private function retrieveInventoriedGeneralFragmentContactFields(array $currentTaskData): array
    {
        $essentialFields = $this->convertEssentialFieldsToCamelCase();

        $currentTaskData = array_intersect_key($currentTaskData, $essentialFields);
        $inventoriedFields = array_keys(array_filter($currentTaskData, fn ($value) => !empty($value)));

        return array_map(fn($f) => TextCaseConvertor::camelToSnake($f), $inventoriedFields);
    }

    /**
     * Helper function to convert array elements to Camel case
     *
     * @return array
     */
    private function convertEssentialFieldsToCamelCase(): array
    {
        $essentialFields = array_map(fn($field) => TextCaseConvertor::snakeToCamel($field), self::ESSENTIAL_FIELDS);
        $essentialFields = array_fill_keys($essentialFields, null);

        return $essentialFields;
    }
}
