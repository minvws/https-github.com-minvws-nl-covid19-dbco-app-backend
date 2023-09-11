<?php

declare(strict_types=1);

namespace Tests\Unit\Services\SearchHash\Slot;

use App\Services\SearchHash\Exception\SlotInvalidException;
use App\Services\SearchHash\Slot\Slots;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\UnitTestCase;

use function array_merge;

#[Group('search-hash')]
final class SlotsTest extends UnitTestCase
{
    use MatchesSnapshots;

    public function testItCanBeInitialized(): void
    {
        $slots = new Slots(
            dateOfBirth: $this->faker->date('d-m-Y'),
            lastThreeBsnDigits: null,
            lastname: null,
            postalCode: null,
            houseNumber: null,
            houseNumberSuffix: null,
            phone: null,
        );
        $this->assertInstanceOf(Slots::class, $slots);
    }

    public function testGetIndexSlots(): void
    {
        $this->assertMatchesSnapshot($this->getSlotsFixture($this->getSlotsDataFixture())->getIndexSlotKeys());
    }

    public function testTaskSlots(): void
    {
        $this->assertMatchesSnapshot($this->getSlotsFixture($this->getSlotsDataFixture())->getTaskSlotKeys());
    }

    public function testGetIndexSlotsGroup1ThrowsAnException(): void
    {
        $this->expectExceptionObject(new SlotInvalidException('Could not determine index slot group 1'));

        $data = array_merge(
            $this->getSlotsDataFixture(),
            ['lastThreeBsnDigits' => null, 'lastname' => null],
        );
        $this->getSlotsFixture($data)->getIndexSlotKeys();
    }

    public function testGetIndexSlotsGroup2ThrowsAnException(): void
    {
        $this->expectExceptionObject(new SlotInvalidException('Could not determine index slot group 2'));

        $data = array_merge(
            $this->getSlotsDataFixture(),
            ['postalCode' => null, 'phone' => null],
        );
        $this->getSlotsFixture($data)->getIndexSlotKeys();
    }

    public function testGetTaskSlotsGroup1ThrowsAnException(): void
    {
        $this->expectExceptionObject(new SlotInvalidException('Could not determine task slot group 1'));

        $data = array_merge(
            $this->getSlotsDataFixture(),
            ['lastThreeBsnDigits' => null, 'lastname' => null],
        );
        $this->getSlotsFixture($data)->getTaskSlotKeys();
    }

    public function testGetTaskSlotsGroup2ThrowsAnException(): void
    {
        $this->expectExceptionObject(new SlotInvalidException('Could not determine task slot group 2'));

        $data = array_merge(
            $this->getSlotsDataFixture(),
            ['postalCode' => null, 'phone' => null],
        );
        $this->getSlotsFixture($data)->getTaskSlotKeys();
    }

    public function testGetIndexSlotsCache(): void
    {
        $slots = $this->getSlotsFixture($this->getSlotsDataFixture());
        $result = $slots->getIndexSlotKeys();

        $this->assertSame($result, $slots->getIndexSlotKeys());
    }

    public function testGetTaskSlotsCache(): void
    {
        $slots = $this->getSlotsFixture($this->getSlotsDataFixture());
        $result = $slots->getTaskSlotKeys();

        $this->assertSame($result, $slots->getTaskSlotKeys());
    }

    protected function getSlotsDataFixture(): array
    {
        return [
            'dateOfBirth' => '01-04-2000',
            'lastThreeBsnDigits' => '123',
            'lastname' => 'Jane',
            'postalCode' => '9999XX',
            'houseNumber' => '01',
            'houseNumberSuffix' => 'A',
            'phone' => '123',
        ];
    }

    protected function getSlotsFixture(array $data): Slots
    {
        return new Slots(...$data);
    }
}
