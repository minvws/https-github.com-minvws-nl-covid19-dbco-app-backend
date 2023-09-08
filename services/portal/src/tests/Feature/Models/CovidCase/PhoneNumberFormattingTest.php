<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class PhoneNumberFormattingTest extends FeatureTestCase
{
    #[DataProvider('phoneProvider')]
    #[Group('phone')]
    #[TestDox('Format phone number $phoneNumberOrigin')]
    public function testPhoneNumberFormattedCorrectly(
        string $phoneNumberOrigin,
        string $phoneNumberExpected,
    ): void {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(
            sprintf('/api/cases/%s/fragments/contact', $case->uuid),
            [
                'phone' => $phoneNumberOrigin,
            ],
        );
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($phoneNumberExpected, $data['data']['phone']);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function phoneProvider(): array
    {
        return [
            ['020 234 56 78', '020 234 5678'],
            ['0202345678', '020 234 5678'],
            ['020-234-56-78', '020 234 5678'],
            ['+31 46 4431234', '046 443 1234'],
            ['0031 46 4431234', '046 443 1234'],
            ['++31 46 4431234', '046 443 1234'],
            ['+31 20 234 56 78', '020 234 5678'],
            ['+31 20 234 56 78', '020 234 5678'],
            ['+31 20 234-56-78', '020 234 5678'],
            ['+31 020 234-56-78', '020 234 5678'],
            ['06 23 41 56 78', '06 23415678'],
            ['06/23 41 56 78', '06 23415678'],
            ['06-23-41-56-78', '06 23415678'],
            ['06-23-41a56-78', '06 23415678'],
            ['+49 (06442) 3933023', '+49 6442 3933023'],
            ['+49 (02852) 5996-0', '+49 2852 59960'],
            ['+49 (042) 1818 87 9919', '+49 421 818879919'],
            ['+49 0644123', '+49 6441 23'],
            ['+49 06442abc', '+49 6442 222'],
        ];
    }
}
