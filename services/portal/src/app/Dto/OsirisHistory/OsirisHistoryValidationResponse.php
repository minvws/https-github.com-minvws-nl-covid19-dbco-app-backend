<?php

declare(strict_types=1);

namespace App\Dto\OsirisHistory;

use App\Schema\Validation\ValidationRules;
use JsonSerializable;

use function array_key_exists;
use function array_merge;

/**
 * @property ?array $errors
 * @property ?array $warnings
 */
class OsirisHistoryValidationResponse implements JsonSerializable
{
    public const TYPE_MAPPING = [
        ValidationRules::WARNING => 'errors',
        ValidationRules::NOTICE => 'warnings',
    ];

    protected ?array $errors;
    protected ?array $warnings;

    public function __construct(
        ?array $errors = null,
        ?array $warnings = null,
    ) {
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    public static function fromValidationResult(array $validationResult): self
    {
        $osirisValidationResponse = new self();
        foreach ($validationResult as $types) {
            foreach ($types as $type => $errorObject) {
                if (array_key_exists($type, self::TYPE_MAPPING)) {
                    $mapped_key = self::TYPE_MAPPING[$type];

                    $osirisValidationResponse->$mapped_key = array_merge(
                        $osirisValidationResponse->$mapped_key ?: [],
                        $errorObject['errors']->all(),
                    );
                }
            }
        }

        return $osirisValidationResponse;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
}
