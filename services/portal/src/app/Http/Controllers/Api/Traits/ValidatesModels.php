<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Traits;

use App\Models\CovidCase\Contracts\Validatable;
use App\Schema\Traits\ValidationTagging;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use function array_combine;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function in_array;
use function is_a;
use function is_array;
use function json_decode;
use function json_encode;
use function strpos;

use const ARRAY_FILTER_USE_KEY;

/**
 * Trait for adding model validation support.
 */
trait ValidatesModels
{
    use ValidationTagging;

    /**
     * Creates a validator for the given severity level.
     *
     * Controllers can override this to customize the validator.
     *
     * @param string $modelClass Model class.
     * @param array $data Model data.
     * @param array $rules Rules retrieved using the fragment class.
     * @param string $severityLevel Severity level.
     */
    protected function createModelValidator(string $modelClass, array $data, array $rules, string $severityLevel): ?ValidatorContract
    {
        if (count($rules) > 0) {
            return Validator::make($data, $rules);
        }

        return null;
    }

    /**
     * Returns the model validation rules index by severity level.
     */
    protected function getModelValidationRules(string $modelClass, array $data, array $filterTags = []): array
    {
        if (is_a($modelClass, Validatable::class, true)) {
            $validationRules = $modelClass::validationRules($data);
            return $this->mapRules($validationRules, $filterTags);
        }

        return [];
    }

    /**
     * Filter data from the data array for which no failed entry exists.
     *
     * NOTE:
     * We currently don't filter repeatables!
     *
     * @param array $data
     * @param array $failed
     *
     * @return array
     */
    private function validatedData(array $data, array $failed): array
    {
        $failedKeys = array_filter(array_keys($failed), static fn (string $k) => strpos($k, '.') === false);
        return array_filter(
            $data,
            static fn($key) => !in_array($key, $failedKeys, true),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Returns the validation results with field keys in a dotted format.
     * For example: 'task.dateOfLastExposure' instead of 'dateOfLastExposure'
     */
    protected function prefixDottedFormat(array $validationResults, string $prefix): mixed
    {
        $json = json_encode($validationResults);
        if (!$json) {
            return $validationResults;
        }

        /** @var array $validationResults */
        $validationResults = json_decode($json, true);

        foreach ($validationResults as $key => $validationResult) {
            if (!is_array($validationResult)) {
                continue;
            }
            foreach ($validationResult as $warning => $messages) {
                if (!in_array($warning, ['failed', 'errors'], true)) {
                    continue;
                }

                $validationResults[$key][$warning] = array_combine(
                    array_map(static function ($k) use ($prefix) {
                        return $prefix . '.' . $k;
                    }, array_keys($messages)),
                    $messages,
                );
            }
        }

        return $validationResults;
    }

    /**
     * @throws ValidationException
     */
    protected function validateModel(
        string $modelClass,
        array $data,
        ?array &$validatedData = null,
        string $validatedDataLevel = Validatable::SEVERITY_LEVEL_FATAL,
        ?array $additionalData = null,
        array $filterTags = [],
        bool $stopOnFirstFailedSeverityLevel = true,
    ): array {
        $rules = $this->getModelValidationRules($modelClass, array_merge($data, $additionalData ?? []), $filterTags);

        return $this->validateModelRules(
            $data,
            $rules,
            $modelClass,
            $additionalData,
            $validatedDataLevel,
            $validatedData,
            $stopOnFirstFailedSeverityLevel,
        );
    }

    /**
     * @throws ValidationException
     */
    protected function validateCreateModel(string $modelClass, array $data, ?array &$validatedData = null, string $validatedDataLevel = Validatable::SEVERITY_LEVEL_FATAL, ?array $additionalData = null): array
    {
        $rules = $this->getModelValidationRules($modelClass, array_merge($data, $additionalData ?? []));

        return $this->validateModelRules($data, $rules, $modelClass, $additionalData, $validatedDataLevel);
    }

    /**
     * @throws ValidationException
     */
    protected function validateUpdateModel(string $modelClass, array $data, ?array &$validatedData = null, string $validatedDataLevel = Validatable::SEVERITY_LEVEL_FATAL, ?array $additionalData = null): array
    {
        $rules = $this->getModelValidationRules($modelClass, array_merge($data, $additionalData ?? []));

        return $this->validateModelRules($data, $rules, $modelClass, $additionalData, $validatedDataLevel, $validatedData);
    }

    /**
     * @throws ValidationException
     */
    private function validateModelRules(
        ?array $data,
        array $rules,
        string $modelClass,
        ?array $additionalData,
        string $validatedDataLevel,
        ?array &$validatedData = null,
        bool $stopOnFirstFailedSeverityLevel = true,
    ): array {
        $severityLevels = [
            Validatable::SEVERITY_LEVEL_FATAL,
            Validatable::SEVERITY_LEVEL_WARNING,
            Validatable::SEVERITY_LEVEL_NOTICE,
        ];

        $result = [];
        $validationData = $data;
        $validationRules = [];
        foreach ($severityLevels as $severityLevel) {
            if (!array_key_exists($severityLevel, $rules)) {
                continue;
            }
            $validationRules = array_merge($validationRules, $rules[$severityLevel] ?? []);
            $validator = $this->createModelValidator(
                $modelClass,
                array_merge($validationData ?? [], $additionalData ?? []),
                $validationRules,
                $severityLevel,
            );

            if ($validator === null) {
                continue;
            }

            if ($validator->fails()) {
                if ($severityLevel === $validatedDataLevel) {
                    $validatedData = $this->validatedData($validationData ?? [], $validator->failed());
                }

                $result[$severityLevel] = [
                    'failed' => $validator->failed(),
                    'errors' => $validator->errors(),
                ];

                if ($stopOnFirstFailedSeverityLevel) {
                    break;
                }

                continue;
            }

            if ($severityLevel === $validatedDataLevel) {
                $validatedData = $validator->validated();
            }

            if ($additionalData === null) {
                continue;
            }

            // additional data is separated from the main date, we can safely use the validated data
            $validationData = $validator->validated();
        }

        return $result;
    }
}
