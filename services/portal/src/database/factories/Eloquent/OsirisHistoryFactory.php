<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Dto\OsirisHistory\OsirisHistoryValidationResponse;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisHistory;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;

class OsirisHistoryFactory extends Factory
{
    protected $model = OsirisHistory::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'case_uuid' => static fn() => EloquentCase::factory()->create(),
            'status' => $this->faker->randomElement([
                OsirisHistoryStatus::success(),
                OsirisHistoryStatus::failed(),
                OsirisHistoryStatus::validation(),
            ]),
            'osiris_status' => $this->faker->randomElement([
                SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL,
                SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
                SoapMessageBuilder::NOTIFICATION_STATUS_DELETED,
            ]),
            'osiris_validation_response' => new OsirisHistoryValidationResponse(
                (array) $this->faker->sentences(),
                (array) $this->faker->sentences(),
            ),
        ];
    }
}
