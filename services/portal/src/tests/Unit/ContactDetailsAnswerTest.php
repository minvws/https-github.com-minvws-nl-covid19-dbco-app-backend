<?php

namespace Tests\Unit;

use App\Models\ContactDetailsAnswer;
use Tests\TestCase;

class ContactDetailsAnswerTest extends TestCase
{
    private const INCOMPLETE = false;
    private const COMPLETE = true;

    /**
     * @testdox Answer with $_dataName gives complete=$isComplete
     * @dataProvider answerValuesProvider
     */
    public function testAnswerProgress(
        ?string $fistname,
        ?string $lastname,
        ?string $email,
        ?string $phonenumber,
        bool $isComplete
    ): void
    {
        $answer = new ContactDetailsAnswer;
        $answer->firstname = $fistname;
        $answer->lastname = $lastname;
        $answer->email = $email;
        $answer->phonenumber = $phonenumber;

        $this->assertSame($isComplete, $answer->isCompleted());
    }

    /**
     * @testdox Answer value is returned verbatim for forms
     */
    public function testAnswerToFormValue(): void
    {
        $answer = new ContactDetailsAnswer();
        $answer->firstname = 'firstname';
        $answer->lastname = 'lastname';
        $answer->email = 'email';
        $answer->phonenumber = '+31234567890';

        $this->assertSame([
           'firstname' => $answer->firstname,
           'lastname' => $answer->lastname,
           'email' => $answer->email,
           'phonenumber' => $answer->phonenumber
        ], $answer->toFormValue());
    }

    public static function answerValuesProvider(): array
    {
        return [
            'null values' => [
                null, null, null, null,
                self::INCOMPLETE
            ],
            'empty values' => [
                '', '', '', '',
                self::INCOMPLETE
            ],
            'name only' => [
                'firstname', 'lastname', '', '',
                self::INCOMPLETE
            ],
            'number only' => [
                '', '', '', '+31234567890',
                self::INCOMPLETE
            ],
            'firstname and number' => [
                'firstname', '', '', '+31234567890',
                self::INCOMPLETE
            ],
            'lastname and number' => [
                '', 'lastname', '', '+31234567890',
                self::INCOMPLETE
            ],
            'name and email' => [
                'firstname', 'lastname', 'pl@ceholder', '',
                self::INCOMPLETE
            ],
            'all fields' => [
                'firstname', 'lastname', 'pl@ceholder', '+31234567890',
                self::COMPLETE
            ]
        ];
    }
}
