<?php

declare(strict_types=1);

namespace App\Services\SearchHash\EloquentTask\General;

use App\Models\Eloquent\EloquentTask;
use App\Services\SearchHash\Attribute\HashSource;
use App\Services\SearchHash\SafeIssetFragment;
use DateTimeInterface;
use DBCO\Shared\Application\Helpers\PhoneFormatter;

use function is_null;

class GeneralHash
{
    use SafeIssetFragment;

    #[HashSource('general.phone')]
    public readonly ?string $phone;

    public function __construct(
        #[HashSource('personalDetails.dateOfBirth')]
        public readonly ?DateTimeInterface $dateOfBirth,
        #[HashSource('general.lastname')]
        public readonly ?string $lastname,
        ?string $phone,
    ) {
        $this->phone = is_null($phone) ? null : PhoneFormatter::format($phone);
    }

    public static function fromTask(EloquentTask $task): self
    {
        $dateOfBirth = $task->personal_details->dateOfBirth;

        return new GeneralHash(
            dateOfBirth: $dateOfBirth,
            lastname: self::getLastname($task),
            phone: self::getPhone($task),
        );
    }

    protected static function getPhone(EloquentTask $task): ?string
    {
        if (!self::issetFragment($task, 'general') || is_null($task->general->phone)) {
            return null;
        }

        return $task->general->phone;
    }

    protected static function getLastname(EloquentTask $task): ?string
    {
        if (!self::issetFragment($task, 'general') || is_null($task->general->lastname)) {
            return null;
        }

        return $task->general->lastname;
    }
}
