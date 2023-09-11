<?php

declare(strict_types=1);

namespace App\Services\SearchHash\Slot;

use App\Services\SearchHash\Exception\SlotInvalidException;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;

use function array_filter;
use function assert;
use function count;
use function is_string;

final class Slots
{
    public DateTimeInterface $dateOfBirth;

    /** @var Collection<int,non-empty-array<int,string>> */
    protected Collection $indexSlotKeys;

    /** @var Collection<int,non-empty-array<int,string>> */
    protected Collection $taskSlotKeys;

    public function __construct(
        DateTimeInterface|string $dateOfBirth,
        public readonly ?string $lastThreeBsnDigits = null,
        public readonly ?string $lastname = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $houseNumber = null,
        public readonly ?string $houseNumberSuffix = null,
        public readonly ?string $phone = null,
    ) {
        if (is_string($dateOfBirth)) {
            $dateOfBirth = CarbonImmutable::createFromFormat('d-m-Y', $dateOfBirth);
        }

        assert($dateOfBirth instanceof DateTimeInterface);

        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return Collection<int,non-empty-array<int,string>>
     */
    public function getIndexSlotKeys(): Collection
    {
        if (isset($this->indexSlotKeys)) {
            return $this->indexSlotKeys;
        }

        $slotGroup1 = array_filter([
            $this->getDobBsnSlot(),
            $this->getDobLastnameSlot(),
        ]);

        if (count($slotGroup1) === 0) {
            throw new SlotInvalidException('Could not determine index slot group 1');
        }

        $slotGroup2 = array_filter([
            $this->getDobAddressSlot(),
            $this->getDobPhoneSlot(),
        ]);

        if (count($slotGroup2) === 0) {
            throw new SlotInvalidException('Could not determine index slot group 2');
        }

        /** @var Collection<int,non-empty-array<int,string>> $indexSlotKeys */
        $indexSlotKeys = Collection::make([$slotGroup1, $slotGroup2]);

        return $this->indexSlotKeys = $indexSlotKeys;
    }

    /**
     * @return Collection<int,non-empty-array<int,string>>
     */
    public function getTaskSlotKeys(): Collection
    {
        if (isset($this->taskSlotKeys)) {
            return $this->taskSlotKeys;
        }

        $slotGroup1 = array_filter([
            $this->getDobBsnSlot(),
            $this->getDobLastnameSlot(),
        ]);

        if (count($slotGroup1) === 0) {
            throw new SlotInvalidException('Could not determine task slot group 1');
        }

        $slotGroup2 = array_filter([
            $this->getDobAddressSlot(),
            $this->getDobPhoneSlot(),
        ]);

        if (count($slotGroup2) === 0) {
            throw new SlotInvalidException('Could not determine task slot group 2');
        }

        /** @var Collection<int,non-empty-array<int,string>> $taskSlotKeys */
        $taskSlotKeys = Collection::make([$slotGroup1, $slotGroup2]);

        return $this->taskSlotKeys = $taskSlotKeys;
    }

    private function getDobBsnSlot(): ?string
    {
        return $this->getSlot('dateOfBirth#lastThreeBsnDigits', 'lastThreeBsnDigits');
    }

    private function getDobLastnameSlot(): ?string
    {
        return $this->getSlot('dateOfBirth#lastname', 'lastname');
    }

    private function getDobAddressSlot(): ?string
    {
        return $this->getSlot('dateOfBirth#houseNumber#houseNumberSuffix#postalCode', 'postalCode', 'houseNumber');
    }

    private function getDobPhoneSlot(): ?string
    {
        return $this->getSlot('dateOfBirth#phone', 'phone');
    }

    private function getSlot(string $slot, string ...$propertyKeys): ?string
    {
        $anyEmpty = Collection::make($propertyKeys)
            ->contains(fn (string $propertyKey): bool => empty($this->{$propertyKey}));

        return $anyEmpty ? null : $slot;
    }
}
