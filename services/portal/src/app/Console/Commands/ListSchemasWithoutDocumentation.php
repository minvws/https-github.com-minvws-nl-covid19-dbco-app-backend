<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use App\Services\Catalog\TypeRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

use function collect;
use function count;
use function sprintf;

class ListSchemasWithoutDocumentation extends Command
{
    /** @var string $signature */
    protected $signature = 'schema:list-without-documentation';

    public function handle(TypeRepository $catalogTypeRepository): int
    {
        $this->info('Started to find missing schema documentation...');

        $catalogTypes = collect($catalogTypeRepository->getTypes());

        $schemas = $catalogTypes->filter(static fn (Type $type) => $type instanceof SchemaType);
        $this->info(sprintf('Analyzing %d schemas...', $schemas->count()));

        return $this->analyzeSchemas($schemas) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param Collection<SchemaType> $schemas
     */
    private function analyzeSchemas(Collection $schemas): bool
    {
        $tableHeaders = ['Name', 'Message'];
        $tableRows = [];

        foreach ($schemas as $schema) {
            $class = '<fg=green>Class: </>' . $schema->getSchemaVersion()->getClass();
            $documentation = $schema->getSchemaVersion()->getDocumentation();

            if (empty($documentation->getLabel())) {
                $tableRows[] = [$class, 'Label is missing.'];
            }

            if (empty($documentation->getShortDescription())) {
                $tableRows[] = [$class, 'ShortDescription is missing.'];
            }

            if (empty($documentation->getDescription())) {
                $tableRows[] = [$class, 'Description is missing.'];
            }

            $fields = $schema->getSchemaVersion()->getFields();
            foreach ($fields as $field) {
                if (empty($field->getDocumentation()->getLabel())) {
                    $fieldName = '<fg=green>Field: </>' . $field->getSchema()->getClass();
                    $tableRows[] = [$fieldName, 'Label is missing.'];
                }
            }
        }

        if (!empty($tableRows)) {
            $this->table($tableHeaders, $tableRows);

            $this->warn(count($tableRows) . ' results found.');

            return false;
        }

        return true;
    }
}
