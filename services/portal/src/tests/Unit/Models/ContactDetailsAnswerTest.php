<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ContactDetailsAnswer;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Unit\UnitTestCase;

class ContactDetailsAnswerTest extends UnitTestCase
{
    #[TestDox('Answer value is returned verbatim for forms')]
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
            'phonenumber' => $answer->phonenumber,
        ], $answer->toFormValue());
    }
}
