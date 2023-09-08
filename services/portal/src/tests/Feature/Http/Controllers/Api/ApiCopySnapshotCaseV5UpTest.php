<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\UnderlyingSuffering;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\CaseFragmentGenerator;

use function dirname;
use function route;

use const DIRECTORY_SEPARATOR;

/**
 * This classes uses snapshots to help you generate the expected data. You can find the snapshots in the
 * snapshots directory in the same directory as this class.
 *
 * If you run the test the first time, when no snapshot is created yet, the test will be marked as skipped.
 * At this point the snapshot is created. If you rerun the test it will assert the current data in the test against
 * the data in the snapshot. If they match the test succeeds and we can be sure the output is still the same.
 *
 * To generate the snapshots you can run the following command
 *
 * bin/phpunit portal --group=snapshot -d --update-snapshots
 */
#[Group('copy')]
#[Group('snapshot')]
class ApiCopySnapshotCaseV5UpTest extends FeatureTestCase
{
    use CaseFragmentGenerator;
    use MatchesSnapshots;

    private EloquentUser $user;
    private EloquentCase $case;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::create(2021, 6, 23, 12));
        CarbonImmutable::setTestNow(CarbonImmutable::create(2021, 6, 23, 12));

        $organisation = $this->createOrganisation(['name' => 'DBCO']);
        $this->user = $this->createUserWithoutOrganisation(['name' => 'Jessica']);
        $this->user->organisations()->attach($organisation->uuid);

        $this->case = $this->createCaseForUser($this->user, [
            'schema_version' => 5,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'bco_status' => BCOStatus::open(),
            'date_of_test' => null,
            'case_id' => '1234321',
            'underlying_suffering' => UnderlyingSuffering::newInstanceWithVersion(
                2,
                static function (UnderlyingSuffering $underlyingSuffering): void {
                    $underlyingSuffering->hasUnderlyingSufferingOrMedication = null;
                    $underlyingSuffering->hasUnderlyingSuffering = null;
                },
            ),
        ]);
    }

    public function testCopyDiagnosticsWithEmptyCase(): void
    {
        $response = $this->be($this->user)->get(route('api-copy-diagnostics', $this->case->uuid));
        $response->assertStatus(200);

        $this->assertMatchesHtmlSnapshot($response->getOriginalContent()->render());
    }

    public function testCopyDiagnosticsWithFullCase(): void
    {
        $this->updateCaseWithAllFragments($this->case);

        $this->case->bco_phase = BCOPhase::phase3();
        $this->case->save();

        $response = $this->be($this->user)->get(route('api-copy-diagnostics', $this->case->uuid));
        $response->assertStatus(200);

        $this->assertMatchesHtmlSnapshot($response->getOriginalContent()->render());
    }

    protected function getSnapshotDirectory(): string
    {
        return dirname((new ReflectionClass($this))->getFileName()) . DIRECTORY_SEPARATOR . 'snapshots';
    }
}
