<?php

declare(strict_types=1);

namespace App\Services\SearchHash\EloquentCase\Contact;

use App\Models\Eloquent\EloquentCase;
use App\Services\SearchHash\Attribute\HashSource;
use App\Services\SearchHash\SafeIssetFragment;
use DateTimeInterface;
use DBCO\Shared\Application\Helpers\PhoneFormatter;

use function is_null;

final class ContactHash
{
    use SafeIssetFragment;

    #[HashSource('contact.phone')]
    public readonly ?string $phone;

    public function __construct(
        #[HashSource('index.dateOfBirth')]
        public readonly ?DateTimeInterface $dateOfBirth,
        ?string $phone,
    ) {
        $this->phone = is_null($phone) ? null : PhoneFormatter::format($phone);
    }

    public static function fromCase(EloquentCase $case): self
    {
        return new ContactHash(
            dateOfBirth: $case->relationLoaded('index') ? $case->index->dateOfBirth : null,
            phone: $case->contact->phone,
        );
    }
}
