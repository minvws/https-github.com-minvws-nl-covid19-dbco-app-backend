<?php

declare(strict_types=1);

namespace App\Http\Server\Soap;

use Illuminate\Http\Request;
use SoapServer as NativeSoapServer;
use Webmozart\Assert\Assert;

use function ini_get;
use function ini_set;
use function ob_get_clean;
use function ob_start;
use function restore_error_handler;
use function set_error_handler;

use const E_USER_ERROR;
use const WSDL_CACHE_NONE;

final class SoapServerProxy implements SoapServer
{
    private ?string $wsdl = null;
    private ?string $class = null;

    public function setWsdl(string $wsdl): void
    {
        $this->wsdl = $wsdl;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function handle(Request $request): string
    {
        $content = $request->getContent();
        Assert::nullOrString($content);

        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        set_error_handler(null, E_USER_ERROR);

        ob_start();
        $this->createSoapServer()->handle($content);
        $response = ob_get_clean();

        restore_error_handler();
        ini_set('display_errors', (string) $displayErrors);

        Assert::string($response);

        return $response;
    }

    private function createSoapServer(): NativeSoapServer
    {
        $nativeSoapServer = new NativeSoapServer($this->wsdl, ['cache_wsdl' => WSDL_CACHE_NONE]);

        if ($this->class !== null) {
            $nativeSoapServer->setClass($this->class);
        }

        return $nativeSoapServer;
    }
}
