<?php

declare(strict_types=1);

namespace App\Services\SearchHash\EloquentTask\General;

use App\Services\SearchHash\AbstractSearchHasher;
use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\Exception\SearchHashRuntimeException;

use function assert;
use function is_null;
use function is_string;

/**
 * @method GeneralHash getValueObject()
 *
 * @extends AbstractSearchHasher<GeneralHash>
 */
final class GeneralSearchHasher extends AbstractSearchHasher
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

    #[HashCombination('dateOfBirth', 'phone')]
    public function getPhoneHash(): string
    {
        assert(!is_null($this->getValueObject()->phone));

        return $this->generateHash([
            $this->getFormattedDateOfBirth(),
            $this->getValueObject()->phone,
        ]);
    }

    protected function getFormattedDateOfBirth(): string
    {
        assert(!is_null($this->getValueObject()->dateOfBirth));

        $formattedDate = $this->getValueObject()->dateOfBirth->format('Ymd');

        assert($formattedDate !== false, new SearchHashRuntimeException('Failed formatting dateOfBirth.'));

        return $formattedDate;
    }
}
