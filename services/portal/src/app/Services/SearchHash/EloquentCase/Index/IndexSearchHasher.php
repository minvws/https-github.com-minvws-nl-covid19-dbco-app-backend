<?php

declare(strict_types=1);

namespace App\Services\SearchHash\EloquentCase\Index;

use App\Services\SearchHash\AbstractSearchHasher;
use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\Exception\SearchHashRuntimeException;

use function assert;
use function is_null;
use function is_string;

/**
 * @method IndexHash getValueObject()
 *
 * @extends AbstractSearchHasher<IndexHash>
 */
final class IndexSearchHasher extends AbstractSearchHasher
{
    #[HashCombination('dateOfBirth', 'lastname')]
    public function getLastnameHash(): string
    {
        assert(is_string($this->getValueObject()->lastname));

        return $this->generateHash([
            $this->getFormattedDateOfBirth(),
            $this->getValueObject()->lastname,
        ]);
    }

    #[HashCombination('dateOfBirth', 'lastThreeBsnDigits')]
    public function getBsnHash(): string
    {
        assert(is_string($this->getValueObject()->lastThreeBsnDigits));

        return $this->generateHash([
            $this->getFormattedDateOfBirth(),
            $this->getValueObject()->lastThreeBsnDigits,
        ]);
    }

    #[HashCombination('dateOfBirth', 'postalCode', 'houseNumber', 'houseNumberSuffix')]
    public function getAddressHash(): string
    {
        assert(is_string($this->getValueObject()->postalCode));
        assert(is_string($this->getValueObject()->houseNumber));

        $values = [
            $this->getFormattedDateOfBirth(),
            $this->getValueObject()->postalCode,
            $this->getValueObject()->houseNumber,
        ];

        $houseNumberSuffix = $this->getValueObject()->houseNumberSuffix;

        if ($houseNumberSuffix !== null) {
            $values[] = $houseNumberSuffix;
        }

        return $this->generateHash($values);
    }

    protected function getFormattedDateOfBirth(): string
    {
        assert(!is_null($this->getValueObject()->dateOfBirth));

        $formattedDate = $this->getValueObject()->dateOfBirth->format('Ymd');

        assert($formattedDate !== false, new SearchHashRuntimeException('Failed formatting dateOfBirth.'));

        return $formattedDate;
    }
}
