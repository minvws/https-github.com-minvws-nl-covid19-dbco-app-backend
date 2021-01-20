<?php
namespace MinVWS\Metrics\Repositories;

use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Models\Export;

/**
 * Responsible for exporting to a file.
 *
 * @package MinVWS\Metrics\Repositories
 */
class CsvExportRepository implements ExportRepository
{
    /**
     * @var string[]
     */
    private array $fields;

    /**
     * Header.
     *
     * @var array|null
     */
    private ?array $labels;

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
    public function openFile(string $path, Export $export)
    {
        $handle = fopen($path, 'w');

        if ($this->labels !== null) {
            fputcsv($handle, $this->labels);
        }

        return $handle;
    }

    /**
     * @inheritdoc
     */
    public function addEventToFile(Event $event, $handle)
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
    public function closeFile($handle)
    {
        fclose($handle);
    }
}