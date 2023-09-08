<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\Guard\X509Guard;
use App\Ldap\LdapDnParser;
use App\Models\AccessRequest;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Place;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Policies\AccessRequestPolicy;
use App\Policies\CallToActionPolicy;
use App\Policies\CaseLabelPolicy;
use App\Policies\ContextPolicy;
use App\Policies\EloquentCasePolicy;
use App\Policies\EloquentTaskPolicy;
use App\Policies\ExpertQuestionPolicy;
use App\Policies\PlacePolicy;
use App\Policies\PolicyGuidelinePolicy;
use App\Policies\PolicyVersionPolicy;
use App\Policies\RiskProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function assert;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        AccessRequest::class => AccessRequestPolicy::class,
        CallToAction::class => CallToActionPolicy::class,
        CaseLabel::class => CaseLabelPolicy::class,
        Context::class => ContextPolicy::class,
        EloquentCase::class => EloquentCasePolicy::class,
        EloquentTask::class => EloquentTaskPolicy::class,
        Place::class => PlacePolicy::class,
        ExpertQuestion::class => ExpertQuestionPolicy::class,
        RiskProfile::class => RiskProfilePolicy::class,
        PolicyGuideline::class => PolicyGuidelinePolicy::class,
        PolicyVersion::class => PolicyVersionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::extend('x509', function ($app, $name, array $config) {
            $userProvider = Auth::createUserProvider($config['provider'] ?? null);
            assert($userProvider !== null);

            $request = $this->app->get('request');
            assert($request instanceof Request);

            $ldapDnParser = $this->app->make(LdapDnParser::class);
            assert($ldapDnParser instanceof LdapDnParser);

            return new X509Guard(
                $userProvider,
                $request,
                $ldapDnParser,
                $config['subjectDnHeaderName'] ?? X509Guard::DEFAULT_SUBJECT_DN_HEADER_NAME,
                $config['subjectDnAttribute'] ?? X509Guard::DEFAULT_SUBJECT_DN_ATTRIBUTE,
                $config['storageKey'] ?? X509Guard::DEFAULT_STORAGE_KEY,
            );
        });
    }
}
