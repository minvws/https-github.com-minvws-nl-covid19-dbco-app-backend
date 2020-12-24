<?php

namespace Tests\Unit;

use App\Models\ClassificationDetailsAnswer;
use Tests\TestCase;

class ClassificationDetailsAnswerTest extends TestCase
{
    private const NOT_CONTACTABLE = false;
    private const CONTACTABLE = true;
    private const INCOMPLETE = false;
    private const COMPLETE = true;

    private const CATEGORY_1 = true;
    private const CATEGORY_2A = true;
    private const CATEGORY_2B = true;
    private const CATEGORY_3 = true;

    /**
     * @testdox Answer with $_dataName gives contactable=$isContactable complete=$isComplete
     * @dataProvider answerValuesProvider
     */
    public function testAnswerProgress(
        bool $category1Risk,
        bool $category2ARisk,
        bool $category2BRisk,
        bool $category3Risk,
        bool $isContactable,
        bool $isComplete
    ): void
    {
        $answer = new ClassificationDetailsAnswer;
        $answer->category1Risk = $category1Risk;
        $answer->category2ARisk = $category2ARisk;
        $answer->category2BRisk = $category2BRisk;
        $answer->category3Risk = $category3Risk;

        $this->assertSame($isContactable, $answer->isContactable());
        $this->assertSame($isComplete, $answer->isCompleted());
    }

    public function answerValuesProvider(): array
    {
        return [
            'all risks' => [
                self::CATEGORY_1, self::CATEGORY_2A, self::CATEGORY_2B, self::CATEGORY_3,
                self::CONTACTABLE, self::COMPLETE
            ],
            'no classification' => [
                false, false, false, false,
                self::NOT_CONTACTABLE, self::INCOMPLETE
            ],
            'category 1' => [
                self::CATEGORY_1, false, false, false,
                self::CONTACTABLE, self::COMPLETE
            ],
            'category 2a' => [
                false, self::CATEGORY_2A, false, false,
                self::CONTACTABLE, self::COMPLETE
            ],
            'category 2b' => [
                false, false, self::CATEGORY_2B, false,
                self::CONTACTABLE, self::COMPLETE
            ],
            'category 3' => [
                false, false, false, self::CATEGORY_3,
                self::CONTACTABLE, self::COMPLETE
            ],
        ];
    }
}
