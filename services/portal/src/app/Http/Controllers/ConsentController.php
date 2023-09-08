<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Config;
use App\Models\Eloquent\EloquentUser;
use App\Services\AuthenticationService;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;

use function abort_unless;
use function redirect;
use function view;

class ConsentController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Toon toestemming')]
    public function showConsent(): View
    {
        $user = $this->authenticationService->getAuthenticatedUser();

        $auditEvent = AuditEvent::create(
            __METHOD__,
            AuditEvent::ACTION_READ,
            AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
        );
        $auditEvent->object(AuditObject::create('user', $user->uuid));

        return $this->auditService->registerHttpEvent($auditEvent, static function () {
            return view('consent');
        });
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Toestemming opgeslagen')]
    public function storeConsent(Request $request): RedirectResponse
    {
        $user = $this->authenticationService->getAuthenticatedUser();

        $auditEvent = AuditEvent::create(
            __METHOD__,
            AuditEvent::ACTION_READ,
            AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
        );
        $auditEvent->object(AuditObject::create('user', $user->uuid));

        return $this->auditService->registerHttpEvent($auditEvent, static function () use ($request, $user) {
            if ($request->has('consent')) {
                $user->consented_at = CarbonImmutable::now();
                $user->save();
            }

            return redirect()->route('root-redirect');
        });
    }

    public function resetConsent(): RedirectResponse
    {
        $this->auditService->setEventExpected(false);

        abort_unless(Config::boolean('auth.allow_demo_login'), Response::HTTP_FORBIDDEN);

        EloquentUser::on()->whereExists(static function ($query): void {
            $query
                ->select(DB::raw(1))
                ->from('user_organisation')
                ->whereColumn('user_organisation.user_uuid', 'bcouser.uuid')
                ->where('user_organisation.organisation_uuid', '=', DummySeeder::DEMO_ORGANISATION_UUID);
        })->update([
            'consented_at' => null,
        ]);

        return redirect()->route('login');
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Toon privacy statement')]
    public function showPrivacyStatement(): View
    {
        $user = $this->authenticationService->getAuthenticatedUser();

        $auditEvent = AuditEvent::create(
            __METHOD__,
            AuditEvent::ACTION_READ,
            AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
        );
        $auditEvent->object(AuditObject::create('user', $user->uuid));

        return $this->auditService->registerHttpEvent($auditEvent, static function () {
            return view('privacy-statement');
        });
    }
}
