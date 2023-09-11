<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\CovidCase\UnderlyingSuffering;
use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\Traits\WithFragments;
use Database\Seeders\OrganisationSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

/**
 * @extends Factory<EloquentCase>
 */
class EloquentCaseFactory extends Factory
{
    use WithFragments;

    protected $model = EloquentCase::class;

    private static ?string $defaultOrganisationUuid = null;

    public function newModel(array $attributes = []): EloquentCase
    {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()
            ->getVersion($attributes['schema_version'] ?? EloquentCase::getSchema()->getCurrentVersion()->getVersion())
            ->newInstance();

        $case->forceFill($attributes);

        return $case;
    }

    private function getDefaultOrganisationUuid(): string
    {
        if (self::$defaultOrganisationUuid === null) {
            /** @var EloquentOrganisation $organisation */
            $organisation = (new EloquentOrganisation())
                ->newQueryWithoutScopes()
                ->where('external_id', OrganisationSeeder::GHOR)
                ->first();

            self::$defaultOrganisationUuid = $organisation->uuid;

            assert(self::$defaultOrganisationUuid !== null);
        }

        return self::$defaultOrganisationUuid;
    }

    public function definition(): array
    {
        if (CarbonImmutable::hasTestNow()) {
            $now = CarbonImmutable::now();
            $createdAt = $now;
            $updatedAt = $now;
        } else {
            $createdAt = new CarbonImmutable($this->faker->dateTimeBetween('-30 days'));
            $updatedAt = $this->faker->dateTimeBetween($createdAt);
        }

        $bcoStatus = $createdAt->diffInDays() > 14 ? BCOStatus::completed() : $this->faker->randomElement([
            BCOStatus::draft(),
            BCOStatus::open(),
            BCOStatus::completed(),
        ]);

        return [
            'uuid' => $this->faker->uuid(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'schema_version' => EloquentCase::getSchema()->getCurrentVersion()->getVersion(),
            'hpzone_number' => (string) $this->faker->unique()->numberBetween(1_000_000, 9_999_999),
            'bco_status' => $bcoStatus,
            'date_of_test' => $this->faker->dateTimeBetween($createdAt->subDays(7), $createdAt->subDays(1)),
            'organisation_uuid' => $this->getDefaultOrganisationUuid(),
            'status_index_contact_tracing' => $this->randomStatusIndexContactTracing(),
            'underlying_suffering' => UnderlyingSuffering::newInstanceWithVersion(
                2,
                static function (UnderlyingSuffering $underlyingSuffering): void {
                    $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
                    $underlyingSuffering->hasUnderlyingSuffering = YesNoUnknown::no();
                },
            ),
            'automatic_address_verification_status' => $this->faker->randomElement(
                AutomaticAddressVerificationStatus::all(),
            ),
            'completed_at' => $bcoStatus === BCOStatus::completed() ? $updatedAt : null,
        ];
    }

    public function withUpdate(): self
    {
        return $this->afterCreating(function (EloquentCase $case): void {
            if ($case->bcoStatus === BCOStatus::completed()) {
                return;
            }

            if ($this->faker->numberBetween(1, 2) !== 1) {
                return;
            }

            /** @var CaseUpdateFactory $caseUpdateFactory */
            $caseUpdateFactory = CaseUpdate::factory();
            $caseUpdateFactory->withFragments()->create(['case_uuid' => $case->uuid]);
        });
    }

    private function randomStatusIndexContactTracing(): ContactTracingStatus
    {
        $statusIndexContactTracing = $this->faker->randomElement(
            [ContactTracingStatus::bcoFinished(), $this->faker->randomElement(ContactTracingStatus::all())],
        );
        assert($statusIndexContactTracing instanceof ContactTracingStatus);
        return $statusIndexContactTracing;
    }
}
