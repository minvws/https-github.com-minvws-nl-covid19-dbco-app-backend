<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use App\Models\StatusIndexContactTracing;
use App\Services\Osiris\NotificationService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

final class NotificationServiceTest extends FeatureTestCase
{
    public function testIsOsirisNotificationRequiredForCaseWhenNoNotificationsHaveBeenSent(): void
    {
        $case = $this->createCase();
        $this->assertTrue(NotificationService::isOsirisNotificationRequiredForCase($case));
    }

    public function testIsIndexContactTracingCompletedForCaseWhenNull(): void
    {
        $case = $this->createCase([
            'status_index_contact_tracing' => null,
        ]);
        $this->assertNull($case->status_index_contact_tracing);
        $this->assertFalse(NotificationService::isIndexContactTracingCompletedForCase($case));
    }

    #[DataProvider('isOsirisNotificationNeededOfRequiredWhenNotificationHasBeenSentDataProvider')]
    public function testIsOsirisNotificationRequiredForCaseWhenInitialNotificationsHasBeenSent(array $caseAttributes, array $notificationAttributes, bool $required): void
    {
        $case = $this->createCase($caseAttributes);
        $this->createOsirisNotificationForCase($case, $notificationAttributes);
        $this->assertEquals($required, NotificationService::isOsirisNotificationRequiredForCase($case));
    }

    #[DataProvider('isOsirisNotificationNeededOfRequiredWhenNotificationHasBeenSentDataProvider')]
    public function testIsFinalOsirisNotificationNeededWhenNotificationHasBeenSent(array $caseAttributes, array $notificationAttributes, bool $required): void
    {
        $case = $this->createCase($caseAttributes);
        $this->createOsirisNotificationForCase($case, $notificationAttributes);
        $this->assertEquals($required, NotificationService::isOsirisFinalNotificationNeededForCase($case));
    }

    public static function isOsirisNotificationNeededOfRequiredWhenNotificationHasBeenSentDataProvider(): array
    {
        $now = CarbonImmutable::now();
        $overdueNotifiedAt = $now->copy()->sub('2 hours');
                // Create case with an overdue osiris notification

        return [
            'Overdue notification, new' => [
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::NEW(),
                ],
                [
                    'notified_at' => $overdueNotifiedAt,
                ],
                false,
            ],
            'Up to date notification, new' => [
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::NEW(),
                ],
                [
                    'notified_at' => $now,
                ],
                false,
            ],
            'Overdue notification, completed' => [
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::completed(),
                ],
                [
                    'notified_at' => $overdueNotifiedAt,
                ],
                true,
            ],
            'Up to date notification, completed' => [
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::completed(),
                ],
                [
                    'notified_at' => $now,
                ],
                true,
            ],
        ];
    }

    #[DataProvider('isFinalOsirisNotificationNeededWhenNoNotificationsHaveBeenSentDataProvider')]
    public function testIsFinalOsirisNotificationNeededWhenNoNotificationsHaveBeenSent(array $caseAttributes, bool $required): void
    {
        $case = $this->createCase($caseAttributes);
        $this->assertEquals($required, NotificationService::isOsirisFinalNotificationNeededForCase($case));
    }

    public static function isFinalOsirisNotificationNeededWhenNoNotificationsHaveBeenSentDataProvider(): array
    {
        $now = CarbonImmutable::now();
        return [
            'StatusIndexContactTracing::NEW()' => [
                [
                    'created_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::NEW(),
                ],
                false,
            ],
            'StatusIndexContactTracing::LOOSE_END()' => [
                [
                    'created_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::LOOSE_END(),
                ],
                false,
            ],
            'StatusIndexContactTracing::COMPLETED()' => [
                [
                    'created_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::COMPLETED(),
                ],
                true,
            ],
            'StatusIndexContactTracing::BCO_FINISHED()' => [
                [
                    'created_at' => $now,
                    'status_index_contact_tracing' => StatusIndexContactTracing::BCO_FINISHED(),
                ],
                true,
            ],
        ];
    }
}
