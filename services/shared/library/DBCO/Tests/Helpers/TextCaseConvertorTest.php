<?php

namespace DBCO\Shared\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use DBCO\Shared\Application\Helpers\TextCaseConvertor;

final class TextCaseConvertorTest extends TestCase
{
    public function testTextConversionFromCamelToSnake()
    {
        $this->assertEquals('date_of_last_exposure', TextCaseConvertor::camelToSnake('dateOfLastExposure'));
    }

    public function testTextConversionFromSnakeToCamel()
    {
        $this->assertEquals('dateOfLastExposure', TextCaseConvertor::snakeToCamel('date_of_last_exposure'));
    }
}
