<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ClassificationDetailsAnswer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Unit\UnitTestCase;

class ClassificationDetailsAnswerTest extends UnitTestCase
{
    #[DataProvider('answerValuesProvider')]
    #[TestDox('Answer $value is returned correctly for use in forms')]
    public function testAnswerToFormValue(?string $value): void
    {
        $answer = new ClassificationDetailsAnswer();
        $answer->value = $value;

        $this->assertSame(['value' => $value], $answer->toFormValue());
    }

    public static function answerValuesProvider(): array
    {
        return [
            [null],
            [''],
            ['2a'],
            ['3a'],
        ];
    }
}
