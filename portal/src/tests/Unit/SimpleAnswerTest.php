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

    public static function answerValuesProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['some_value', true]
        ];
    }
}
