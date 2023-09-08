<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Http\Server\Soap\SoapServer;
use App\Repositories\Osiris\OsirisMockRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use RuntimeException;

use function sprintf;

final class SoapMockService
{
    public function __construct(
        private readonly SoapServer $soapServer,
        private readonly string $wsdlPath,
    ) {
    }

    public function loadWsdl(): string
    {
        if (!File::isFile($this->wsdlPath)) {
            throw new RuntimeException(sprintf('File does not exist: "%s"', $this->wsdlPath));
        }

        return File::get($this->wsdlPath);
    }

    public function handleRequest(Request $request): string
    {
        $this->soapServer->setClass(OsirisMockRepository::class);

        return $this->soapServer->handle($request);
    }
}
