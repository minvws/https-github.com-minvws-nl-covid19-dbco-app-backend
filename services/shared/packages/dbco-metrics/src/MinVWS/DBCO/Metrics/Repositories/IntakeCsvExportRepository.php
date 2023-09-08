<?php

namespace MinVWS\DBCO\Metrics\Repositories;

use MinVWS\Metrics\Models\Export;
use MinVWS\DBCO\Metrics\Models\Intake;
use MinVWS\Metrics\Repositories\CsvExportRepository;

/**
 * Responsible for exporting to a CSV file.
 *
 * @package MinVWS\Metrics\Repositories
 */
class IntakeCsvExportRepository extends CsvExportRepository
{
    /**
     * @inheritdoc
     */
    public function addHeaderToStream(Export $export, $handle)
    {
        if ($this->labels !== null) {
            $headerLabels = $this->getIntakeHeaderLabels();
            fputcsv($handle, $headerLabels);
        }
    }

    public function addObjectToStream($intake, $handle)
    {
        $data = [];
        foreach ($this->fields as $fieldKey => $field) {
            if (is_array($field)) {
                $data = $this->getSubFieldData($field, $intake, $fieldKey, $data);
            } else {
                $fieldData = $intake->data[$field] ?? '';
                $data[] = (string)is_array($fieldData) ? json_encode($fieldData) : $fieldData;
            }
        }

        fputcsv($handle, $data);
    }

    private function getSubFieldData(array $field, Intake $intake, string $fieldKey, array $data): array
    {
        foreach ($field as $subField) {
            $subFieldData = $intake->data[$fieldKey][$subField] ?? '';
            if (!is_string($subFieldData)) {
                $subFieldData = json_encode($subFieldData);
            }
            $data[] = $subFieldData;
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getIntakeHeaderLabels(): array
    {
        foreach ($this->labels as $headerKey => $headerValue) {
            if (is_array($headerValue)) {
                foreach ($headerValue as $subHeader) {
                    $headerLabels[] = $headerKey . '.' . $subHeader;
                }
            } else {
                $headerLabels[] = $headerValue;
            }
        }

        return $headerLabels;
    }
}
