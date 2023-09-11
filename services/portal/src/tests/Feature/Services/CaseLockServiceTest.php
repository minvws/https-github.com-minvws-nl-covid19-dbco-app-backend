<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Exceptions\CaseLock\CaseLockFoundException;
use App\Exceptions\CaseLock\CaseLockNotFoundException;
use App\Exceptions\CaseLock\CaseLockNotOwnedException;
use App\Services\CaseLockService;
use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

use function app;
use function config;

class CaseLockServiceTest extends FeatureTestCase
{
    public CaseLockService $caseLockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseLockService = app(CaseLockService::class);
    }

    public function testAbleToCreateACaseLock(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        $user = $this->createUser();
        $case = $this->createCase();

        $this->caseLockService->addCaseLock($case, $user);

        $this->assertDatabaseHas('case_lock', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
            'locked_at' => CarbonImmutable::now(),
        ]);
    }

    public function testUnableToCreateACaseLockWhenLockAlreadyExists(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $this->createCaseLockForCase($case);

        $this->expectExceptionObject(new CaseLockFoundException());
        $this->caseLockService->addCaseLock($case, $user);
    }

    public function testCaseHasCaseLock(): void
    {
        $case = $this->createCase();

        $this->createCaseLockForCase($case);

        $this->assertTrue($this->caseLockService->hasCaseLock($case));
    }

    public function testCaseDoesNotHasCaseLockWhenLockedAtIsEarlierThenSpecifiedTime(): void
    {
        $case = $this->createCase();

        $this->createCaseLockForCase($case, [
            'locked_at' => CarbonImmutable::now()->subMinutes((int) config('misc.caseLock.lifetime', 3) + 1),
        ]);

        $this->assertFalse($this->caseLockService->hasCaseLock($case));
    }

    public function testCaseDoesNotHasCaseLockWhenLockedAtIsAnDayEarlier(): void
    {
        $case = $this->createCase();

        $this->createCaseLockForCase($case, [
            'locked_at' => CarbonImmutable::now()->subDay(),
        ]);

        $this->assertFalse($this->caseLockService->hasCaseLock($case));
    }

    public function testCaseDoesNotHasCaseLock(): void
    {
        $case = $this->createCase();

        $this->assertFalse($this->caseLockService->hasCaseLock($case));
    }

    public function testCaseHasCaseLockCanExcludeCaseLockForSpecifiedUser(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $this->createCaseLockForCaseAndUser($case, $user);

        $this->assertFalse($this->caseLockService->hasCaseLock($case, $user));
    }

    public function testCaseHasCaseLockWillNotExcludeCaseLockIfOtherUserHasBeenGiven(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $this->createCaseLockForCaseAndUser($case, $user);

        $this->assertTrue($this->caseLockService->hasCaseLock($case, $this->createUser()));
    }

    public function testCanRefreshCaseLock(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $caseLock = $this->createCaseLockForCaseAndUser($case, $user, [
            'locked_at' => CarbonImmutable::now()->subMinute(),
        ]);

        CarbonImmutable::setTestNow(CarbonImmutable::now());

        $this->caseLockService->refreshCaseLock($case, $user);

        $this->assertDatabaseHas('case_lock', [
            'uuid' => $caseLock->uuid,
            'locked_at' => CarbonImmutable::now(),
        ]);
    }

    public function testCanNotRefreshCaseLockIfNotExisting(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $this->expectExceptionObject(new CaseLockNotFoundException());
        $this->caseLockService->refreshCaseLock($case, $user);
    }

    public function testCanNotRefreshCaseLockUserIsNotTheOwner(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $this->createCaseLockForCaseAndUser($case, $user);

        $this->expectExceptionObject(new CaseLockNotOwnedException());
        $this->caseLockService->refreshCaseLock($case, $this->createUser());
    }

    public function testCanDeleteCaseLock(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $caseLock = $this->createCaseLockForCaseAndUser($case, $user);

        $this->caseLockService->removeCaseLock($case, $user);

        $this->assertDatabaseMissing('case_lock', [
            'uuid' => $caseLock->uuid,
        ]);
    }

    public function testCanNotDeleteCaseLockIfNotExisting(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $this->expectExceptionObject(new CaseLockNotFoundException());
        $this->caseLockService->removeCaseLock($case, $user);
    }

    public function testCanNotDeleteCaseLockIfUserIsNotTheOwner(): void
    {
        $user = $this->createUser();
        $case = $this->createCase();

        $this->createCaseLockForCaseAndUser($case, $user);

        $this->expectExceptionObject(new CaseLockNotOwnedException());
        $this->caseLockService->removeCaseLock($case, $this->createUser());
    }

    public function testCanCleanCaseLocksWhichAreExpiredAndNotUsedAnymore(): void
    {
        // Outdated caseLock
        $this->createCaseLock([
            'locked_at' => CarbonImmutable::now()->subMinutes(config('misc.caseLock.lifetime') + 1),
        ]);

        $this->caseLockService->cleanCaseLocks();
        $this->assertDatabaseCount('case_lock', 0);
    }

    public function testCannotCleanCaseLocksWhichAreActiveAndStillInUse(): void
    {
        // Outdated caseLock
        $this->createCaseLock([
            'locked_at' => CarbonImmutable::now(),
        ]);

        $this->caseLockService->cleanCaseLocks();
        $this->assertDatabaseCount('case_lock', 1);
    }
}
