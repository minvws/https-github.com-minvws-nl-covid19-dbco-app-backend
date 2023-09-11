<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\TestResult;

use App\Models\Eloquent\TestResult;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class TestResultEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof TestResult);

        $container->uuid = $value->uuid;
        $container->typeOfTest = $value->type_of_test;
        $container->customTypeOfTest = $value->customTypeOfTest;
        $container->dateOfTest = $value->dateOfTest;
        $container->source = $value->source;
        $container->dateOfResult = $value->dateOfResult;
        $container->receivedAt = $value->receivedAt;
        $container->testLocation = $value->general->testLocation;
        $container->sampleLocation = $value->sample_location;
        $container->sampleNumber = $value->monsterNumber;
        $container->laboratory = $value->laboratory;
        $container->result = $value->result;
    }
}
