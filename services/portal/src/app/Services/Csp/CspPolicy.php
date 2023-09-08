<?php

declare(strict_types=1);

namespace App\Services\Csp;

use App\Helpers\Environment;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Policy;
use Symfony\Component\HttpFoundation\Response;

use function is_file;

class CspPolicy extends Policy
{
    public function __construct(protected Repository $config)
    {
    }

    public function configure(): void
    {
        $hosts = [Keyword::SELF];

        if (Environment::isDevelopment() && is_file(Vite::hotFile())) {
            // Allow to connect to and fetch assets from the Vite development server
            $hosts[] = 'http://127.0.0.1:5173';
            $hosts[] = 'ws://127.0.0.1:5173';
        }

        $this->addDirective(Directive::BASE, $hosts)
            ->addDirective(Directive::DEFAULT, $hosts)
            ->addDirective(Directive::STYLE, $hosts)
            ->addDirective(Directive::STYLE, 'unsafe-inline')
            ->addDirective(Directive::FONT, 'https://fonts.googleapis.com https://fonts.gstatic.com')
            ->addDirective(Directive::STYLE, 'https://fonts.googleapis.com')
            ->addDirective(Directive::CONNECT, $hosts)
            ->addDirective(Directive::FORM_ACTION, $hosts)
            ->addDirective(Directive::IMG, $hosts)
            ->addDirective(Directive::IMG, 'data:')
            ->addDirective(Directive::MEDIA, $hosts)
            ->addDirective(Directive::OBJECT, Keyword::NONE)
            ->addDirective(Directive::SCRIPT, $hosts)
            ->addDirective(Directive::SCRIPT, Keyword::UNSAFE_EVAL)
            ->addNonceForDirective(Directive::SCRIPT);
    }

    public function shouldBeApplied(Request $request, Response $response): bool
    {
        if ($this->config->get('app.debug') && ($response->isClientError() || $response->isServerError())) {
            $this->reportOnly();
        }

        return parent::shouldBeApplied($request, $response);
    }
}
