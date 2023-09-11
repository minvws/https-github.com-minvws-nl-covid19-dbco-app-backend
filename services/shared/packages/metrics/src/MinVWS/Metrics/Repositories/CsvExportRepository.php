<?php

namespace MinVWS\Metrics\Repositories;

use MinVWS\Metrics\Models\Export;
use MinVWS\Metrics\Models\Intake;

/**
 * Responsible for exporting to a CSV file.
 *
 * @package MinVWS\Metrics\Repositories
 */
class CsvExportRepository implements ExportRepository
{
    /**
     * @var string[]
     */
    protected array $fields;

    /**
     * Header.
     *
     * @var array|null
     */
    protected ?array $labels;

    /**
     * Constructor.
     *
     * @param string[]   $fields
     * @param array|null $labels
     */
    public function __construct(array $fields, ?array $labels = null)
    {
        $this->fields = $fields;
        $this->labels = $labels;
    }

    /**
     * @inheritdoc
     */
    public function addHeaderToStream(Export $export, $handle)
    {
        if ($this->labels !== null) {
            fputcsv($handle, $this->labels);
        }
    }

    /**
     * @inheritdoc
     */
    public function addObjectToStream($event, $handle)
    {
        $data = [];
        foreach ($this->fields as $field) {
            $data[] = (string)($event->exportData[$field] ?? '');
        }

        fputcsv($handle, $data);
    }

    /**
     * @inheritdoc
     */
    public function addFooterToStream(Export $export, $handle)
    {
        // don't do anything
    }
}
