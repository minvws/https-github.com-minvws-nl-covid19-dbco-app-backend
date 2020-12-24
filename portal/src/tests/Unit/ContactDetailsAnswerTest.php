<?php

namespace Tests\Unit;

use App\Models\ContactDetailsAnswer;
use Tests\TestCase;

class ContactDetailsAnswerTest extends TestCase
{
    private const NOT_CONTACTABLE = false;
    private const CONTACTABLE = true;
    private const INCOMPLETE = false;
    private const COMPLETE = true;

    /**
     * @testdox Answer with $_dataName gives contactable=$isContactable complete=$isComplete
     * @dataProvider answerValuesProvider
     */
    public function testAnswerProgress(
        ?string $fistname,
        ?string $lastname,
        ?string $email,
        ?string $phonenumber,
        bool $isContactable,
        bool $isComplete
    ): void
    {
        $answer = new ContactDetailsAnswer;
        $answer->firstname = $fistname;
        $answer->lastname = $lastname;
        $answer->email = $email;
        $answer->phonenumber = $phonenumber;

        $this->assertSame($isContactable, $answer->isContactable());
        $this->assertSame($isComplete, $answer->isCompleted());
    }

    public function answerValuesProvider(): array
    {
        return [
            'null values' => [
                null, null, null, null,
                self::NOT_CONTACTABLE, self::INCOMPLETE
            ],
            'empty values' => [
                '', '', '', '',
                self::NOT_CONTACTABLE, self::INCOMPLETE
            ],
            'name only' => [
                'firstname', 'lastname', '', '',
                self::NOT_CONTACTABLE, self::INCOMPLETE
            ],
            'number only' => [
                '', '', '', '+31234567890',
                self::NOT_CONTACTABLE, self::INCOMPLETE
            ],
            'firstname and number' => [
                'firstname', '', '', '+31234567890',
                self::CONTACTABLE, self::INCOMPLETE
            ],
            'lastname and number' => [
                '', 'lastname', '', '+31234567890',
                self::CONTACTABLE, self::INCOMPLETE
            ],
            'name and email' => [
                'firstname', 'lastname', 'pl@ceholder', '',
                self::NOT_CONTACTABLE, self::INCOMPLETE
            ],
            'all fields' => [
                'firstname', 'lastname', 'pl@ceholder', '+31234567890',
                self::CONTACTABLE, self::COMPLETE
            ]
        ];
    }
}
