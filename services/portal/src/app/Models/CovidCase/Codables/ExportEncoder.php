<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\ValueTypeMismatchException;
use stdClass;

class ExportEncoder
{
    private Encoder $encoder;

    public function __construct()
    {
        $this->encoder = new Encoder();
    }

    public function getEncoder(): Encoder
    {
        return $this->encoder;
    }

    public function getContext(): EncodingContext
    {
        return $this->encoder->getContext();
    }

    /**
     * @throws ValueTypeMismatchException
     */
    public function encode(mixed $data): object
    {
        return $this->encoder->encode($data) ?? new stdClass();
    }
}
