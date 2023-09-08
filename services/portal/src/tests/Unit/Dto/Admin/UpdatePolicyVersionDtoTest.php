<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\UpdatePolicyVersionDto;
use Carbon\CarbonImmutable;
use PhpOption\None;
use PhpOption\Some;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('policy')]
#[Group('policyVersion')]
class UpdatePolicyVersionDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new UpdatePolicyVersionDto(
            name: None::create(),
            status: None::create(),
            startDate: None::create(),
        );

        $this->assertInstanceOf(UpdatePolicyVersionDto::class, $dto);
    }

    #[DataProvider('getToArrayData')]
    public function testToArray(array $data, array $expected): void
    {
        $this->assertEqualsCanonicalizing($expected, (new UpdatePolicyVersionDto(...$data))->toArray());
    }

    public static function getToArrayData(): array
    {
        $startDate = CarbonImmutable::createFromDate('2021', 1, 1);

        return [
            'returns an empty array if it does not have some values' => [
                'data' => [
                    'name' => None::create(),
                    'status' => None::create(),
                    'startDate' => None::create(),
                ],
                'expected' => [],
            ],
            'returns name and startDate' => [
                'data' => [
                    'name' => new Some('my name'),
                    'status' => None::create(),
                    'startDate' => new Some($startDate),
                ],
                'expected' => [
                    'name' => 'my name',
                    'start_date' => $startDate,
                ],
            ],
            'returns all values' => [
                'data' => [
                    'name' => new Some('my name'),
                    'status' => new Some('my status'),
                    'startDate' => new Some($startDate),
                ],
                'expected' => [
                    'name' => 'my name',
                    'statis' => 'my status',
                    'start_date' => $startDate,
                ],
            ],
        ];
    }
}
