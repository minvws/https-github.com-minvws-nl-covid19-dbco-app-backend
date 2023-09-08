<?php

declare(strict_types=1);

namespace App\Models\Fields;

use function array_key_exists;
use function array_map;
use function array_shift;
use function assert;
use function file;
use function is_array;

class PlainCSVContentRetriever implements ProvidesPurposeSpecificationArray
{
    private static array $purposeCache = [];

    public function __construct(
        public string $filePath,
    ) {
    }

    private function readCsv(): array
    {
        $fileContent = file($this->filePath);
        assert(is_array($fileContent));
        $data = array_map('str_getcsv', $fileContent);
        array_shift($data);

        return FieldPurposeSpecificationArrayBuilder::build($data);
    }

    public function getContent(): array
    {
        if (array_key_exists($this->filePath, self::$purposeCache)) {
            return self::$purposeCache[$this->filePath];
        }

        $content = $this->readCsv();
        self::$purposeCache[$this->filePath] = $content;
        return $content;
    }
}
