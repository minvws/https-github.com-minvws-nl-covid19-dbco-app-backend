<?php

declare(strict_types=1);

namespace App\Services\SearchHash\EloquentCase\Contact;

use App\Services\SearchHash\AbstractSearchHasher;
use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\Exception\SearchHashRuntimeException;

use function assert;
use function is_null;

/**
 * @method ContactHash getValueObject()
 *
 * @extends AbstractSearchHasher<ContactHash>
 */
final class ContactSearchHasher extends AbstractSearchHasher
{
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
