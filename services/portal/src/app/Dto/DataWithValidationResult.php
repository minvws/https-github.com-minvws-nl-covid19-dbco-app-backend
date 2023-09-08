<?php

declare(strict_types=1);

namespace App\Dto;

use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

use function count;

class DataWithValidationResult implements Encodable
{
    /** @var mixed */
    private $data;
    private array $validationResult;

    /**
     * @param array $validationResult
     */
    public function __construct(mixed $data, array $validationResult)
    {
        $this->data = $data;
        $this->validationResult = $validationResult;
    }

    public function encode(EncodingContainer $container): void
    {
        $container->data = $this->data;
        if (count($this->validationResult) > 0) {
            $container->validationResult = $this->validationResult;
        }
    }
}
