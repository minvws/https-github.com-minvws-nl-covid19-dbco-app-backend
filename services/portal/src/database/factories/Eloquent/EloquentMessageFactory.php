<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;

class EloquentMessageFactory extends Factory
{
    protected $model = EloquentMessage::class;

    public function definition(): array
    {
        $createdAt = new CarbonImmutable($this->faker->dateTimeBetween('-1 months'));

        return [
            'uuid' => $this->faker->uuid(),
            'created_at' => $createdAt,
            'case_created_at' => $createdAt,
            'user_uuid' => static function () {
                return EloquentUser::factory()->create();
            },
            'case_uuid' => static function () use ($createdAt) {
                return EloquentCase::factory()->create([
                    'created_at' => $createdAt,
                ]);
            },
            'mail_template' => $this->faker->word(),
            'message_template_type' => $this->faker->randomElement(MessageTemplateType::all()),
            'mail_language' => $this->faker->randomElement(EmailLanguage::all()),
            'mailer_identifier' => $this->faker->optional()->uuid(),
            'from_name' => $this->faker->name(),
            'from_email' => $this->faker->safeEmail(),
            'to_name' => $this->faker->name(),
            'to_email' => $this->faker->safeEmail(),
            'telephone' => $this->faker->optional()->phoneNumber(),
            'subject' => $this->faker->sentence(),
            'text' => $this->faker->paragraph(),
            'status' => $this->faker->word(),
            'pseudo_bsn' => $this->faker->optional()->uuid(),
            'identity_required' => $this->faker->boolean(),
            'is_secure' => $this->faker->boolean(),
            'notification_sent_at' => $this->faker->optional()->dateTime(),
            'expires_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
