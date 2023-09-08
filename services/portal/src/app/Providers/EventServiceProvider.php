<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Api\Export\InvalidJWTEncountered;
use App\Events\CalendarItemConfigStrategyCreated;
use App\Events\CalendarItemConfigStrategyUpdated;
use App\Events\CalendarItemCreated;
use App\Events\Case\CaseOrganisationUpdated;
use App\Events\Case\CaseUpdatedByPlanner;
use App\Events\FailedX509Authentication;
use App\Events\JobHandled;
use App\Events\OpenAPI\OpenAPIValidationFailedEvent;
use App\Events\Osiris\CaseExportFailed;
use App\Events\Osiris\CaseExportRejected;
use App\Events\Osiris\CaseExportSucceeded;
use App\Events\Osiris\CaseNotExportable;
use App\Events\Osiris\CaseValidationRaisesNotice;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Events\Osiris\ExportableCaseNotFound;
use App\Events\PolicyGuidelineCreated;
use App\Events\PolicyVersionCreated;
use App\Events\RateLimiter\RateLimiterHit;
use App\Listeners\Api\Export\InvalidJwtEncounteredListener;
use App\Listeners\Audit\MeasureAuditEventFailure;
use App\Listeners\Audit\MeasureAuditEventSize;
use App\Listeners\Auth\FailedX509LoginListener;
use App\Listeners\InitializeCalendarItemConfigStrategies;
use App\Listeners\JobResultSubscriber;
use App\Listeners\OpenAPIValidationFailedEventLogger;
use App\Listeners\Osiris\CreateOsirisHistory;
use App\Listeners\Osiris\CreateOsirisNotification;
use App\Listeners\Osiris\JobHandledListener;
use App\Listeners\Osiris\LogOsirisExportSuccess;
use App\Listeners\Osiris\LogOsirisValidationResult;
use App\Listeners\Osiris\MeasureOsirisExportFailure;
use App\Listeners\Osiris\MeasureRateLimiterHit;
use App\Listeners\Osiris\ReopenCase;
use App\Listeners\Osiris\SendDefinitiveAnswersToOsiris;
use App\Listeners\PopulateCalendarItemConfigFromCalendarItem;
use App\Listeners\PopulateCalendarItemConfigFromPolicyGuideline;
use App\Listeners\PopulatePolicyVersion;
use App\Listeners\UpdateCalendarItemConfigStrategies;
use App\Models\Eloquent\Moment;
use App\Observers\MomentObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use MinVWS\Audit\Events\AuditEventRegistered;
use MinVWS\Audit\Events\AuditEventSchemaDeviates;
use MinVWS\Audit\Events\AuditEventSchemaMissing;
use MinVWS\Audit\Events\AuditEventSpecDeviates;
use MinVWS\Audit\Events\AuditEventSpecMissing;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // ExportAPI events
        FailedX509Authentication::class => [
            FailedX509LoginListener::class,
        ],
        InvalidJWTEncountered::class => [
            InvalidJwtEncounteredListener::class,
        ],

        // Case change events
        CaseOrganisationUpdated::class => [
            [SendDefinitiveAnswersToOsiris::class, 'whenCaseIsArchived'],
        ],
        CaseUpdatedByPlanner::class => [
            [SendDefinitiveAnswersToOsiris::class, 'whenCaseIsArchived'],
        ],

        // Osiris Pre-Export Events
        ExportableCaseNotFound::class => [
            [MeasureOsirisExportFailure::class, 'whenCaseForExportIsMissing'],
        ],
        CaseNotExportable::class => [
            [CreateOsirisHistory::class, 'whenCaseNotExportable'],
        ],
        CaseValidationRaisesWarning::class => [
            [CreateOsirisHistory::class, 'whenCaseValidationRaisesWarning'],
            [LogOsirisValidationResult::class, 'whenCaseValidationRaisesWarning'],
            [ReopenCase::class, 'whenCaseValidationRaisesWarning'],
        ],
        CaseValidationRaisesNotice::class => [
            [LogOsirisValidationResult::class, 'whenCaseValidationRaisesNotice'],
        ],
        RateLimiterHit::class => [
            MeasureRateLimiterHit::class,
        ],

        // Osiris Post-Export Events
        CaseExportSucceeded::class => [
            LogOsirisExportSuccess::class,
            CreateOsirisNotification::class,
            [CreateOsirisHistory::class, 'whenCaseExportSucceeded'],
        ],
        CaseExportRejected::class => [
            [CreateOsirisHistory::class, 'whenCaseExportWasRejected'],
            [MeasureOsirisExportFailure::class, 'whenCaseExportWasRejected'],
            [ReopenCase::class, 'whenCaseExportWasRejected'],
        ],
        CaseExportFailed::class => [
            [CreateOsirisHistory::class, 'whenExportClientEncounteredError'],
            [MeasureOsirisExportFailure::class, 'whenExportClientEncounteredError'],
            [ReopenCase::class, 'whenExportClientEncounteredError'],
        ],
        JobHandled::class => [
            JobHandledListener::class,
        ],

        // Audit Event Validation
        AuditEventSchemaMissing::class => [
            [MeasureAuditEventFailure::class, 'whenSchemaIsMissing'],
        ],
        AuditEventSchemaDeviates::class => [
            [MeasureAuditEventFailure::class, 'whenSchemaDeviates'],
        ],
        AuditEventSpecMissing::class => [
            [MeasureAuditEventFailure::class, 'whenSpecIsMissing'],
        ],
        AuditEventSpecDeviates::class => [
            [MeasureAuditEventFailure::class, 'whenSpecDeviates'],
        ],
        AuditEventRegistered::class => [
            MeasureAuditEventSize::class,
        ],

        // OpenAPI
        OpenAPIValidationFailedEvent::class => [
            OpenAPIValidationFailedEventLogger::class,
        ],

        // Admin
        PolicyVersionCreated::class => [
            PopulatePolicyVersion::class,
        ],
        CalendarItemCreated::class => [
            PopulateCalendarItemConfigFromCalendarItem::class,
        ],
        PolicyGuidelineCreated::class => [
            PopulateCalendarItemConfigFromPolicyGuideline::class,
        ],
        CalendarItemConfigStrategyCreated::class => [
            InitializeCalendarItemConfigStrategies::class,
        ],
        CalendarItemConfigStrategyUpdated::class => [
            UpdateCalendarItemConfigStrategies::class,
        ],
    ];

    /**
     * Do not use this for versioned objects, because the model will be different
     * (e.g. TaskV1 instead of a EloquentTask)
     */
    protected $observers = [
        Moment::class => [MomentObserver::class],
    ];

    protected $subscribe = [
        JobResultSubscriber::class,
    ];
}
