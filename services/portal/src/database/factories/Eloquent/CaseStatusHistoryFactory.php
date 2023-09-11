<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CaseStatusHistory;
use App\Models\Eloquent\EloquentCase;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\BCOStatus;

use function config;
use function random_int;

class CaseStatusHistoryFactory extends Factory
{
    protected $model = CaseStatusHistory::class;

    /**
     * @throws Exception
     */
    public function definition(): array
    {
        /** @var string|int $numDaysInPast */
        $numDaysInPast = config('casemetrics.created_archived_days_in_past');

        return [
            'uuid' => $this->faker->uuid(),
            'covidcase_uuid' => static function () {
                return EloquentCase::factory()->create();
            },
            'bco_status' => [BCOStatus::draft(), BCOStatus::completed(), BCOStatus::archived()][random_int(0, 2)],
            'changed_at' => $this->faker->dateTimeBetween("-$numDaysInPast days"),
        ];
    }
}
