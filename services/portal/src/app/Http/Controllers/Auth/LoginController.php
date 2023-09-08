<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Exceptions\InvalidIdentityHubUserException;
use App\Http\Controllers\Controller;
use App\Models\Eloquent\EloquentUser;
use App\Providers\Auth\IdentityHubProvider;
use App\Providers\Auth\IdentityHubUser;
use App\Providers\Auth\SessionExpiredException;
use App\Services\AuthenticationService;
use App\Services\UserService;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Models\AuditUser;
use MinVWS\Audit\Services\AuditService;
use MinVWS\DBCO\Enum\Models\Permission;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use function config;
use function is_string;
use function redirect;
use function request;
use function response;
use function view;

class LoginController extends Controller
{
    private const DEMO_USERS_DEFAULT = [
        [
            ['uuid' => DummySeeder::DEMO_USER_UUID, 'label' => 'Demo GGD1: Gebruiker'],
            ['uuid' => DummySeeder::DEMO_USER_PLANNER_UUID, 'label' => 'Demo GGD1: Gebruiker & Werkverdeler'],
            ['uuid' => DummySeeder::DEMO_PLANNER_UUID, 'label' => 'Demo GGD1: Werkverdeler'],
            [
                'uuid' => DummySeeder::DEMO_USER_CASEQUALITY_PLANNER_UUID,
                'label' => 'Demo GGD1: Gebruiker & Werkverdeler & Dossierchecker',
            ],
            ['uuid' => DummySeeder::DEMO_CLUSTERSPECIALIST_UUID, 'label' => 'Demo GGD1: Clusterspecialist'],
            ['uuid' => DummySeeder::DEMO_CALLCENTER_EXPERT_UUID, 'label' => 'Demo GGD1: Callcenter Expert'],
            ['uuid' => DummySeeder::DEMO_CASEQUALITY_UUID, 'label' => 'Demo GGD1: Dossierchecker'],
            ['uuid' => DummySeeder::DEMO_ADMIN_UUID, 'label' => 'GGD GHOR: Beheerder'],
        ],
    ];

    private const DEMO_USERS_OTHER_ROLES = [
        [
            ['uuid' => DummySeeder::DEMO_COMPLIANCE_UUID, 'label' => 'Demo GGD1: Compliance Officer'],
            ['uuid' => DummySeeder::DEMO_CALLCENTER_UUID, 'label' => 'Demo GGD1: Callcenter Basis'],
            ['uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_UUID, 'label' => 'Demo GGD1: Medische Supervisor'],
            ['uuid' => DummySeeder::DEMO_USER_CALLCENTER_UUID, 'label' => 'Demo GGD1: Gebruiker & Callcenter Basis'],
            ['uuid' => DummySeeder::DEMO_CONVERSATION_COACH_UUID, 'label' => 'Demo GGD1: Gesprekscoach'],
            ['uuid' => DummySeeder::DEMO_USER_CALLCENTER_EXPERT_UUID, 'label' => 'Demo GGD1: Gebruiker & Callcenter Expert'],
            ['uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_CONVERSATION_COACH_UUID, 'label' => 'Demo GGD1: Medische Supervisor & Gesprekscoach'],
            ['uuid' => DummySeeder::DEMO_USER_CLUSTERSPECIALIST_UUID, 'label' => 'Demo GGD1: Gebruiker & Clusterspecialist'],
            ['uuid' => DummySeeder::DEMO_USER_MEDICAL_SUPERVISOR_UUID, 'label' => 'Demo GGD1: Gebruiker & Medische Supervisor'],
            ['uuid' => DummySeeder::DEMO_USER_CASEQUALITY_UUID, 'label' => 'Demo GGD1: Gebruiker & Dossierchecker'],
            ['uuid' => DummySeeder::DEMO_DATACATALOG, 'label' => 'Demo GGD1: Datacatalogus'],
            ['uuid' => DummySeeder::DEMO_CASEQUALITY_PLANNER_UUID, 'label' => 'Demo GGD1: Werkverdeler & Dossierchecker'],
            ['uuid' => DummySeeder::DEMO_NOROLE_UUID, 'label' => 'Demo GGD1: Geen Rol'],
        ],
        [
            ['uuid' => DummySeeder::DEMO_TWO_USER_UUID, 'label' => 'Demo GGD2: Gebruiker'],
            ['uuid' => DummySeeder::DEMO_TWO_MEDICAL_SUPERVISOR_UUID, 'label' => 'Demo GGD2: Medische Supervisor'],
            ['uuid' => DummySeeder::DEMO_TWO_PLANNER_UUID, 'label' => 'Demo GGD2: Werkverdeler'],
            ['uuid' => DummySeeder::DEMO_TWO_USER_MEDICAL_SUPERVISOR_UUID, 'label' => 'Demo GGD2: Gebruiker & Medische Supervisor'],
            ['uuid' => DummySeeder::DEMO_TWO_CLUSTERSPECIALIST_UUID, 'label' => 'Demo GGD2: Clusterspecialist'],
            ['uuid' => DummySeeder::DEMO_TWO_CONVERSATION_COACH_UUID, 'label' => 'Demo GGD2: Gesprekscoach'],
            ['uuid' => DummySeeder::DEMO_TWO_CASEQUALITY_UUID, 'label' => 'Demo GGD2: Dossierchecker'],
            ['uuid' => DummySeeder::DEMO_TWO_CALLCENTER_UUID, 'label' => 'Demo GGD2: Callcenter Basis'],
            ['uuid' => DummySeeder::DEMO_TWO_CONTEXTMANAGER_UUID, 'label' => 'Demo GGD2: Contextbeheerder'],
            ['uuid' => DummySeeder::DEMO_TWO_CALLCENTER_EXPERT_UUID, 'label' => 'Demo GGD2: Callcenter Expert'],
        ],
        [
            ['uuid' => DummySeeder::DEMO_OUTSOURCE_USER_UUID, 'label' => 'Demo LS1: Gebruiker'],
            ['uuid' => DummySeeder::DEMO_OUTSOURCE_USER_TWO_UUID, 'label' => 'Demo LS2: Gebruiker'],
            ['uuid' => DummySeeder::DEMO_OUTSOURCE_PLANNER_UUID, 'label' => 'Demo LS1: Werkverdeler'],
            ['uuid' => DummySeeder::DEMO_OUTSOURCE_PLANNER_TWO_UUID, 'label' => 'Demo LS2: Werkverdeler'],
            ['uuid' => DummySeeder::DEMO_OUTSOURCE_CASEQUALITY_UUID, 'label' => 'Demo LS1: Dossierchecker'],
            ['uuid' => DummySeeder::DEMO_OUTSOURCE_CASEQUALITY_TWO_UUID, 'label' => 'Demo LS2: Dossierchecker'],
            ['uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_UUID, 'label' => 'Demo LS1: Medische Supervisor'],
            ['uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_TWO_UUID, 'label' => 'Demo LS2: Medische Supervisor'],
            ['uuid' => DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_UUID, 'label' => 'Demo LS1: Gesprekscoach'],
            ['uuid' => DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_TWO_UUID, 'label' => 'Demo LS2: Gesprekcoach'],
        ],
        [
            ['uuid' => DummySeeder::DEMO_CONTEXTMANAGER_UUID, 'label' => 'Demo GGD1: Contextbeheerder'],
            [
                'uuid' => DummySeeder::DEMO_PLANNER_CONTEXTMANAGER_UUID,
                'label' => 'Demo GGD1: Werkverdeler & Contextbeheerder',
            ],
            ['uuid' => DummySeeder::DEMO_USER_CONTEXTMANAGER_UUID, 'label' => 'Demo GGD1: Gebruiker & Contextbeheerder'],
            [
                'uuid' => DummySeeder::DEMO_USER_PLANNER_CONTEXTMANAGER_UUID,
                'label' => 'Demo GGD1: Gebruiker & Werkverdeler & Contextbeheerder',
            ],
            [
                'uuid' => DummySeeder::DEMO_USER_PLANNER_CONTEXTMANAGER_CASEQUALITY_UUID,
                'label' => 'Demo GGD1: Gebruiker & Werkverdeler & Contextbeheerder & Dossierchecker',
            ],
        ],
    ];

    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly UserService $userService,
        private readonly AuditService $auditService,
        private readonly LoggerInterface $logger,
        private readonly Store $session,
    ) {
    }

    public function authenticated(EloquentUser $user): RedirectResponse
    {
        if ($user->getRolesArray() === [Permission::datacatalog()->value]) {
            return redirect()->route('catalog');
        }

        return redirect()->intended();
    }

    /**
     * Redirect the user to the IdentityHub authentication page.
     */
    public function redirectToProvider(): SymfonyResponse
    {
        $this->auditService->setEventExpected(false);
        return Socialite::driver('identityhub')->redirect();
    }

    /**
     * Obtain the user information from IdentityHub.
     */
    #[SetAuditEventDescription('Login callback Identityhub')]
    public function handleProviderCallback(Request $request): SymfonyResponse
    {
        try {
            if ($this->hasInvalidState($request)) {
                throw new InvalidStateException();
            }

            /** @var IdentityHubProvider $provider */
            $provider = Socialite::driver('identityhub');

            // let the provider operate stateless, as the state check is done manually
            $provider->stateless();

            /** @var IdentityHubUser $socialiteUser */
            $socialiteUser = $provider->user();
        } catch (SessionExpiredException $sessionExpiredException) {
            $this->auditService->setEventExpected(false);
            $this->logoutSessions();
            return $this->redirectToProvider();
        } catch (InvalidStateException) {
            $this->logger->error(
                'Authentication failed because of a state mismatch between request and session.',
                [
                    'stateInSession' => $request->session()->pull('state', 'none'),
                    'stateInRequest' => $request->input('state', 'none'),
                    'host' => $request->getSchemeAndHttpHost(),
                    'referer' => $request->server('HTTP_REFERER'),
                    'userAgent' => $request->userAgent(),
                ],
            );
            $this->auditService->setEventExpected(false);
            $this->logoutSessions();
            return redirect()->route('login');
        }

        try {
            $user = $this->userService->upsertUserBySocaliteUser($socialiteUser);
        } catch (InvalidIdentityHubUserException) {
            $this->auditService->setEventExpected(false);
            $this->logoutSessions();
            return response(view('no-access'));
        }

        Auth::login($user, false);

        $user->last_login_at = CarbonImmutable::now();
        $user->save();


        /** @var Route $route */
        $route = request()->route();
        $this->auditService->startEvent(
            AuditEvent::create(
                $route->getActionName(),
                AuditEvent::ACTION_EXECUTE,
                AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
            )
                ->object(AuditObject::create('user', $user->getAuthIdentifier())),
        );

        if (empty($user->roles)) {
            return response(view('no-access'));
        }

        return $this->authenticated($user);
    }

    public function stubAuthenticate(Request $request): SymfonyResponse
    {
        $this->auditService->setEventExpected(false);

        if (!config('auth.allow_demo_login')) {
            return response('', Response::HTTP_NOT_FOUND);
        }

        $demoUuid = $request->input('uuid');
        if (empty($demoUuid)) {
            return response('', Response::HTTP_BAD_REQUEST);
        }

        $authUser = EloquentUser::find($demoUuid);
        if (!$authUser instanceof EloquentUser) {
            return response('', Response::HTTP_FORBIDDEN);
        }

        if (!config('auth.allow_demo_login_all_organisations')) {
            $organisationUuids = [
                DummySeeder::DEMO_ORGANISATION_UUID,
                DummySeeder::DEMO_ORGANISATION_TWO_UUID,
                DummySeeder::DEMO_OUTSOURCE_ORGANISATION_UUID,
                DummySeeder::DEMO_OUTSOURCE_ORGANISATION_TWO_UUID,
            ];

            if ($authUser->organisations->whereNotIn('uuid', $organisationUuids)->isNotEmpty()) {
                return response('', Response::HTTP_FORBIDDEN);
            }
        }

        Auth::login($authUser, false);

        $authUser->last_login_at = CarbonImmutable::now();
        $authUser->save();

        if (empty($authUser->roles)) {
            return response(view('no-access'));
        }

        return $this->authenticated($authUser);
    }

    /**
     * Logout
     *
     * @throws AuthenticationException
     */
    #[SetAuditEventDescription('Uitgelogd')]
    public function logout(AuditEvent $auditEvent): SymfonyResponse
    {
        $user = $this->authService->getAuthenticatedUser();

        $auditEvent->actionCode(AuditEvent::ACTION_EXECUTE)
            ->user(AuditUser::create('portal', $user->externalId)->name($user->name))
            ->object(AuditObject::create('user', $user->uuid));

        $this->logoutSessions();

        return redirect()->intended('/');
    }

    /**
     * Login
     */
    #[SetAuditEventDescription('Ingelogd')]
    public function login(): Application|Factory|View|RedirectResponse
    {
        $this->auditService->setEventExpected(false);

        if (Auth::user() !== null) {
            return redirect()->intended('/');
        }

        $demoUsersDefault = config('auth.allow_demo_login') ? self::DEMO_USERS_DEFAULT : [];
        $demoUsersOtherRoles = config('auth.allow_demo_login') ? self::DEMO_USERS_OTHER_ROLES : [];

        return view('login', [
            'demoUsersDefault' => $demoUsersDefault,
            'demoUsersOtherRoles' => $demoUsersOtherRoles,
            'environmentName' => config('app.env_name'),
        ]);
    }

    private function logoutSessions(): void
    {
        /** @var IdentityHubProvider $identityHub */
        $identityHub = Socialite::driver('identityhub');
        $identityHub->logout();
        Auth::logout();
    }

    private function hasInvalidState(Request $request): bool
    {
        $state = $this->session->get('state');

        if (!is_string($state)) {
            return true;
        }

        return $request->input('state') !== $state;
    }
}
