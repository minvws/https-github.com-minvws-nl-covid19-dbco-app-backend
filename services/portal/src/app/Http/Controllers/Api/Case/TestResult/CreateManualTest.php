<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Case\TestResult;

use DateTimeInterface;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;

class CreateManualTest implements Decodable
{
    public TestResultTypeOfTest $typeOfTest;
    public ?string $customTypeOfTest = null;
    public TestResultResult $result;
    public ?string $monsterNumber = null;
    public DateTimeInterface $dateOfTest;
    public ?string $laboratory = null;

    public static function decode(DecodingContainer $container, ?object $object = null): CreateManualTest|static
    {
        /** @var static $self */
        $self = $object ?? new self();

        if ($container->contains('typeOfTest')) {
            /** @var TestResultTypeOfTest $typeOfTest */
            $typeOfTest = $container->typeOfTest->decodeObject(TestResultTypeOfTest::class);
            $self->typeOfTest = $typeOfTest;
        }

        if ($container->contains('customTypeOfTest')) {
            $self->customTypeOfTest = $container->customTypeOfTest->decodeString();
        }

        if ($container->contains('result')) {
            /** @var TestResultResult $result*/
            $result = $container->result->decodeObject(TestResultResult::class);
            $self->result = $result;
        }

        if ($container->contains('monsterNumber')) {
            $self->monsterNumber = $container->monsterNumber->decodeString();
        }

        if ($container->contains('dateOfTest')) {
            $self->dateOfTest = $container->dateOfTest->decodeDateTime();
        }

        if ($container->contains('laboratory')) {
            $self->laboratory = $container->laboratory->decodeString();
        }

        return $self;
    }
}
