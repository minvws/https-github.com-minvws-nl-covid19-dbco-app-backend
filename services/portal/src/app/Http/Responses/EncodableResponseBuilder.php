<?php

declare(strict_types=1);

namespace App\Http\Responses;

use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContext;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

class EncodableResponseBuilder
{
    private mixed $data;
    private int $status;
    private array $headers;
    private EncodingContext $context;
    private int $jsonOptions = JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES;

    private function __construct(mixed $data, int $status = EncodableResponse::HTTP_OK, array $headers = [])
    {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
        $this->context = new EncodingContext();
    }

    public static function create(mixed $data, int $status = 200, array $headers = []): self
    {
        return new self($data, $status, $headers);
    }

    public function status(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function withContext(callable $callback): self
    {
        $callback($this->context);
        return $this;
    }

    public function jsonOptions(int $options): self
    {
        $this->jsonOptions = $options;
        return $this;
    }

    /**
     * @param class-string $class
     */
    public function registerDecorator(string $class, EncodableDecorator $decorator): self
    {
        $this->context->registerDecorator($class, $decorator);
        return $this;
    }

    public function build(): EncodableResponse
    {
        return new EncodableResponse($this->data, $this->status, $this->headers, $this->context, $this->jsonOptions);
    }
}
