<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use App\Events\Osiris\CaseValidationRaisesNotice;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Listeners\Osiris\LogOsirisValidationResult;
use Generator;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class EventServiceProviderTest extends FeatureTestCase
{
    #[DataProvider('provideEventsAndListeners')]
    public function testEventBindings(): void
    {
        Event::fake();

        Event::assertListening(
            CaseValidationRaisesNotice::class,
            [LogOsirisValidationResult::class, 'whenCaseValidationRaisesNotice'],
        );
        Event::assertListening(
            CaseValidationRaisesWarning::class,
            [LogOsirisValidationResult::class, 'whenCaseValidationRaisesWarning'],
        );
    }

    public static function provideEventsAndListeners(): Generator
    {
        yield '`CaseValidationRaisesNotice` event' => [CaseValidationRaisesNotice::class, [LogOsirisValidationResult::class, 'whenCaseValidationRaisesNotice']];
        yield '`CaseValidationRaisesWarning` event' => [CaseValidationRaisesWarning::class, [LogOsirisValidationResult::class, 'whenCaseValidationRaisesWarning']];
    }
}
