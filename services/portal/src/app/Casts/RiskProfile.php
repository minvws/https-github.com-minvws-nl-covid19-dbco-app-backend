<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Policy\RiskProfile as RiskProfileModel;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Webmozart\Assert\Assert;

use function is_string;
use function sprintf;

class RiskProfile implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string,mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        Assert::isInstanceOf($model, RiskProfileModel::class);

        if ($value instanceof IndexRiskProfile || $value instanceof ContactRiskProfile) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        return match ($model->person_type_enum) {
            PolicyPersonType::index() => IndexRiskProfile::from($value),
            PolicyPersonType::contact() => ContactRiskProfile::from($value),

            default => $this->getRiskProfileOrThrowException($value),
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string,mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        Assert::isInstanceOf($model, RiskProfileModel::class);

        if ($value instanceof IndexRiskProfile || $value instanceof ContactRiskProfile) {
            return $value->value;
        }

        return $value;
    }

    private function getRiskProfileOrThrowException(string $value): IndexRiskProfile|ContactRiskProfile
    {
        return IndexRiskProfile::tryFrom($value)
            ?? ContactRiskProfile::tryFrom($value)
            ?? throw new InvalidArgumentException(sprintf('Invalid value "%s"', $value));
    }
}
