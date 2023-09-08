<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use Tests\Faker\WithFaker;

use function array_merge;

class PolicyVersionSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        self::setupFaker();

        $now = CarbonImmutable::now();

        // Resetting everything:
        PolicyVersion::query()->delete();

        /** @var PolicyVersion $policyVersion */
        // 2 active-soon:
        $policyVersion = $this->getBasePolicyFactory()
            ->createOneQuietly([
                'status' => PolicyVersionStatus::activeSoon(),
                'start_date' => static fn (): CarbonImmutable => $now->addHours(4),
            ]);

        $this->createCalendarItems($policyVersion);

        /** @var PolicyVersion $policyVersion */
        $policyVersion = $this->getBasePolicyFactory()
            ->createOneQuietly([
                'status' => PolicyVersionStatus::activeSoon(),
                'start_date' => static fn (): CarbonImmutable => $now->addHours(36),
            ]);

        // 1 active
        /** @var PolicyVersion $policyVersion */
        $policyVersion = $this->getBasePolicyFactory()
            ->createOneQuietly([
                'status' => PolicyVersionStatus::active(),
                'start_date' => $now->subDay(),
            ]);

        $this->createCalendarItems($policyVersion);

        // We dont use ->count(x) because we want to reset unique after each single create action and not as a bulk
        // action after all creations:
        for ($i = 0; $i < 5; $i++) {
            // 5 draft:
            /** @var PolicyVersion $policyVersion */
            $policyVersion = $this->getBasePolicyFactory()
                ->createOneQuietly([
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => fn (): DateTimeInterface => $this->faker->dateTimeBetween($now, $now->addMonths(6)),
                ]);

            $this->createCalendarItems($policyVersion);

            // 5 old:
            /** @var PolicyVersion $policyVersion */
            $policyVersion = $this->getBasePolicyFactory()
                ->createOneQuietly([
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => fn (): DateTimeInterface => $this->faker->dateTimeBetween($now->subYear(), $now->subWeek()),
                ]);

            $this->createCalendarItems($policyVersion);
        }
    }

    private function getBasePolicyFactory(): Factory
    {
        $faker = $this->faker;

        return PolicyVersion::factory()
            ->has(
                RiskProfile::factory()
                    ->count(4)
                    // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic -- state's closure needs to be bound to the factory
                    ->state(function (array $attributes, PolicyVersion $policyVersion) use ($faker) {
                        return [
                            'risk_profile_enum' => $faker->unique()->randomElement(
                                array_merge(IndexRiskProfile::all(), ContactRiskProfile::all()),
                            ),
                            'policy_guideline_uuid' => PolicyGuideline::factory()->recycle($policyVersion),
                        ];
                    }),
            )
            ->afterCreating(function (PolicyVersion $policyVersion): void {
                $this->faker->unique(true);
            });
    }

    private function createCalendarItems(PolicyVersion $policyVersion): void
    {
        CalendarItem::factory()->recycle($policyVersion)->count(4)->create();
    }
}
