<?php

namespace Tests\Unit;

use App\Models\ClassificationDetailsAnswer;
use Tests\TestCase;

class ClassificationDetailsAnswerTest extends TestCase
{
    private const INCOMPLETE = false;
    private const COMPLETE = true;

    private const CATEGORY_1 = true;
    private const CATEGORY_2A = true;
    private const CATEGORY_2B = true;
    private const CATEGORY_3 = true;

    /**
     * @testdox Answer with $_dataName gives complete=$isComplete
     * @dataProvider answerValuesProvider
     */
    public function testAnswerProgress(
        bool $category1Risk,
        bool $category2ARisk,
        bool $category2BRisk,
        bool $category3Risk,
        bool $isComplete
    ): void
    {
        $answer = new ClassificationDetailsAnswer;
        $answer->category1Risk = $category1Risk;
        $answer->category2ARisk = $category2ARisk;
        $answer->category2BRisk = $category2BRisk;
        $answer->category3Risk = $category3Risk;

        $this->assertSame($isComplete, $answer->isCompleted());
    }

    /**
     * @testdox Answer $_dataName gives classification $formCategory for forms
     * @dataProvider answerValuesForFormProvider
     */
    public function testAnswerValuesToForm(
        bool $category1Risk,
        bool $category2ARisk,
        bool $category2BRisk,
        bool $category3Risk,
        ?string $formCategory
    ): void
    {
        $answer = new ClassificationDetailsAnswer;
        $answer->category1Risk = $category1Risk;
        $answer->category2ARisk = $category2ARisk;
        $answer->category2BRisk = $category2BRisk;
        $answer->category3Risk = $category3Risk;

        $this->assertSame(['value' => $formCategory], $answer->toFormValue());
    }

    public static function answerValuesProvider(): array
    {
        return [
            'all risks' => [
                self::CATEGORY_1, self::CATEGORY_2A, self::CATEGORY_2B, self::CATEGORY_3,
                self::COMPLETE
            ],
            'no classification' => [
                false, false, false, false,
                self::INCOMPLETE
            ],
            'category 1' => [
                self::CATEGORY_1, false, false, false,
                self::COMPLETE
            ],
            'category 2a' => [
                false, self::CATEGORY_2A, false, false,
                self::COMPLETE
            ],
            'category 2b' => [
                false, false, self::CATEGORY_2B, false,
                self::COMPLETE
            ],
            'category 3' => [
                false, false, false, self::CATEGORY_3,
                self::COMPLETE
            ],
        ];
    }

    public static function answerValuesForFormProvider(): array
    {
        return [
            'category 1' => [
                self::CATEGORY_1, false, false, false,
                '1'
            ],
            'category 2a' => [
                false, self::CATEGORY_2A, false, false,
                '2a'
            ],
            'category 2b' => [
                false, false, self::CATEGORY_2B, false,
                '2b'
            ],
            'category 3' => [
                false, false, false, self::CATEGORY_3,
                '3'
            ],
            'category 1+2a+2b+3' => [
                self::CATEGORY_1, self::CATEGORY_2A, self::CATEGORY_2B, self::CATEGORY_3,
                '1'
            ],
            'category 2a+2b+3' => [
                false , self::CATEGORY_2A, self::CATEGORY_2B, self::CATEGORY_3,
                '2a'
            ],
            'category 2b+3' => [
                false , false, self::CATEGORY_2B, self::CATEGORY_3,
                '2b'
            ],
        ];
    }
}
