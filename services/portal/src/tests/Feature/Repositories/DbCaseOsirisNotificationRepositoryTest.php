<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Repositories\DbCaseOsirisNotificationRepository;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('osiris')]
#[Group('osiris-notification')]
final class DbCaseOsirisNotificationRepositoryTest extends FeatureTestCase
{
    public function testCasesWithoutOsirisNotificationsAreAlsoSelected(): void
    {
        $retryFromDate = CarbonImmutable::parse($this->faker->dateTimeBetween('-2 year', '-1 year'));
        $createdAt = $this->faker->dateTimeBetween($retryFromDate);

        $caseWithNotification = $this->createCaseExportableToOsiris([
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt),
        ]);
        $notifiedAt = $this->faker->dateTimeBetween($retryFromDate);
        $this->createOsirisNotificationForCase($caseWithNotification, ['notified_at' => $notifiedAt]);

        $caseWithoutNotification = $this->createCaseExportableToOsiris([
            'created_at' => CarbonImmutable::instance($createdAt)->subMinute(),
            'updated_at' => $this->faker->dateTimeBetween($createdAt),
        ]);

        $cases = (new DbCaseOsirisNotificationRepository($retryFromDate->toDateTimeString()))
            ->getUpdatedCasesWithoutRecentOsirisNotification();

        $this->assertCount(2, $cases);
        $this->assertEquals($caseWithoutNotification->uuid, $cases->first()->uuid);
    }

    public function testCaseWithUpToDateOsirisNotificationsWillNotReturnResults(): void
    {
        $retryFromDate = CarbonImmutable::parse($this->faker->dateTimeBetween('-2 year', '-1 year'));
        $now = CarbonImmutable::now();

        $case = $this->createCaseExportableToOsiris([
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->createOsirisNotificationForCase($case, ['notified_at' => $now->addMinute()]);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $this->faker->dateTimeBetween('-2 hours', $now),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL,
        ]);

        $cases = (new DbCaseOsirisNotificationRepository($retryFromDate->toDateTimeString()))
            ->getUpdatedCasesWithoutRecentOsirisNotification();

        $this->assertCount(0, $cases);
    }

    public function testCasesWithOlderOsirisNotificationsAreSelected(): void
    {
        $case = $this->createCaseExportableToOsiris([
            'created_at' => $this->faker->dateTimeBetween('-1 month'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ]);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $this->faker->dateTimeBetween('-3 months', '-2 month'),
        ]);

        $cases = (new DbCaseOsirisNotificationRepository('2022-12-21 00:00:00'))
            ->getUpdatedCasesWithoutRecentOsirisNotification();

        $this->assertCount(1, $cases);
        $this->assertEquals($case->uuid, $cases->first()->uuid);
    }

    public function testOldCaseReceivesUpdateWillNotReturnResults(): void
    {
        $retryFromDate = CarbonImmutable::parse($this->faker->dateTimeBetween('-2 year', '-1 year'));
        $testDate = $retryFromDate->subDay();

        $case = $this->createCaseExportableToOsiris([
            'created_at' => $testDate,
            'updated_at' => $testDate,
        ]);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $testDate,
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL,
        ]);

        $cases = (new DbCaseOsirisNotificationRepository($retryFromDate->toDateTimeString()))
            ->getUpdatedCasesWithoutRecentOsirisNotification();

        $this->assertCount(0, $cases);
    }

    public function testCaseWithHPZoneNumberWillNotBeFound(): void
    {
        $case = $this->createCase([
            'created_at' => $this->faker->dateTimeBetween('-1 month'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
            'hpzone_number' => $this->faker->randomNumber(6),
        ]);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $this->faker->dateTimeBetween('-3 months', '-2 month'),
        ]);

        $cases = (new DbCaseOsirisNotificationRepository('2022-12-21 00:00:00'))
            ->getUpdatedCasesWithoutRecentOsirisNotification();

        $this->assertCount(0, $cases);
    }

    public function testFindRetryableDeletedCases(): void
    {
        $retryFromDate = CarbonImmutable::parse($this->faker->dateTimeBetween('-2 year', '-1 year'));

        $this->createCase(); // Non-deleted case
        $this->createCase(['created_at' => $retryFromDate->subMinute()]); // Case created before `retryFromDate`
        $this->createCase(['hpzone_number' => $this->faker->numerify('#######')]); // Case with `hpzone_number`

        $expectedCase = $this->createCaseExportableToOsiris([
            'created_at' => $retryFromDate->addMinute(),
            'deleted_at' => $this->faker->dateTime(),
        ]);

        $collection = (new DbCaseOsirisNotificationRepository($retryFromDate->toDateTimeString()))
            ->findRetryableDeletedCases();

        $this->assertCount(1, $collection);

        $actualCase = $collection->first();

        $this->assertNotNull($actualCase);
        $this->assertEquals($expectedCase->uuid, $actualCase->uuid);
    }

    public function testFindLatestDeletedStatusNotificationReturnsNull(): void
    {
        $case = $this->createCase();
        $this->createOsirisNotificationForCase($case, ['osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL]);
        $this->createOsirisNotificationForCase($case, ['osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE]);

        $notification = (new DbCaseOsirisNotificationRepository($this->faker->dateTime()->format('Y-m-d H:i:s')))
            ->findLatestDeletedStatusNotification($case);

        $this->assertNull($notification);
    }

    public function testFindLatestDeletedStatusNotificationReturnsLatestNotification(): void
    {
        $notifiedAt = CarbonImmutable::parse($this->faker->dateTime());
        $case = $this->createCase();
        $this->createOsirisNotificationForCase($case, [
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DELETED,
            'notified_at' => $notifiedAt->toDateTimeString(),
        ]);
        $expected = $this->createOsirisNotificationForCase($case, [
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DELETED,
            'notified_at' => $notifiedAt->addMinute()->toDateTimeString(),
        ]);

        $notification = (new DbCaseOsirisNotificationRepository($this->faker->dateTime()->format('Y-m-d H:i:s')))
            ->findLatestDeletedStatusNotification($case);

        $this->assertNotNull($notification);
        $this->assertEquals($expected->uuid, $notification->uuid);
    }
}
