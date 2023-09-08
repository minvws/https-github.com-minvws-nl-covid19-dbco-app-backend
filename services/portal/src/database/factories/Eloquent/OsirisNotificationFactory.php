<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisNotification;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\BCOStatus;

class OsirisNotificationFactory extends Factory
{
    protected $model = OsirisNotification::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'case_uuid' => static function () {
                return EloquentCase::factory()->create();
            },
            'notified_at' => $this->faker->dateTime(),
            'bco_status' => $this->faker->randomElement([
                BCOStatus::open(),
                BCOStatus::completed(),
                BCOStatus::archived(),
            ]),
            'osiris_status' => $this->faker->randomElement([
                SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL,
                SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
                SoapMessageBuilder::NOTIFICATION_STATUS_DELETED,
            ]),
            'osiris_questionnaire_version' => 10,
        ];
    }
}
