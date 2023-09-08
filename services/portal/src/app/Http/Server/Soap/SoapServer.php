<?php

declare(strict_types=1);

namespace App\Http\Server\Soap;

use Illuminate\Http\Request;

interface SoapServer
{
    public function setWsdl(string $wsdl): void;

    public function setClass(string $class): void;

    public function handle(Request $request): string;
}
