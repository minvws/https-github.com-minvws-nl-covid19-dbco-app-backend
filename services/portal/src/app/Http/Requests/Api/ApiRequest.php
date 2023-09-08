<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\DecodingContainer;

use function is_array;
use function is_bool;
use function is_string;
use function sprintf;

class ApiRequest extends FormRequest
{
    public function getDecodingContainer(): DecodingContainer
    {
        $decoder = new Decoder();
        return $decoder->decode($this->validated());
    }

    protected function failedValidation(Validator $validator): void
    {
        throw (new ValidationException($validator))
            ->errorBag($this->errorBag);
    }

    public function getArray(string $key): array
    {
        $value = $this->get($key);

        if (!is_array($value)) {
            throw new InvalidFormatException(sprintf('parameter "%s" should be an array', $key));
        }

        return $value;
    }

    public function getBoolean(string $key, ?bool $default = null): bool
    {
        $value = $this->get($key, $default);

        if (!is_bool($value)) {
            throw new InvalidFormatException(sprintf('parameter "%s" should be a boolean', $key));
        }

        return $value;
    }

    public function getString(string $key): string
    {
        $value = $this->get($key);

        if (!is_string($value)) {
            throw new InvalidFormatException(sprintf('parameter "%s" should be a string', $key));
        }

        return $value;
    }

    public function getStringOrNull(string $key): ?string
    {
        $value = $this->get($key);

        return is_string($value) ? $value : null;
    }
}
