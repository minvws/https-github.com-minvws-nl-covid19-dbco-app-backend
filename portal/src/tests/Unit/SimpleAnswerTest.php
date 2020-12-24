<?php

namespace Tests\Unit;

use App\Models\SimpleAnswer;
use Tests\TestCase;

class SimpleAnswerTest extends TestCase
{
    /**
     * @testdox Answer $value gives completed=$isCompleted
     * @dataProvider answerValuesProvider
     */
    public function testAnswerProgress(?string $value, bool $isCompleted): void
    {
        $answer = new SimpleAnswer;
        $answer->value = $value;

        $this->assertSame($isCompleted, $answer->isCompleted());
    }

    /**
     * @testdox Answer $value is returned correctly for use in forms
     * @dataProvider answerValuesProvider
     */
    public function testAnswerToFormValue(?string $value): void
    {
        $answer = new SimpleAnswer;
        $answer->value = $value;

        $this->assertSame($value, $answer->toFormValue());
    }

    public static function answerValuesProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['some_value', true]
        ];
    }
}
