<?php

namespace MinVWS\Metrics\Models;

use MinVWS\Metrics\Models\ExportConfig\Encryption;
use MinVWS\Metrics\Models\ExportConfig\Signature;

class ExportConfig
{
    public string $basePath;
    public string $filenameTemplate;
    public string $filenameTimestampFormat;
    public Encryption $encryption;
    public Signature $signature;
    public array $dbConnectionTypeMapping;

    public function __construct(
        string $basePath,
        string $filenameTemplate,
        string $filenameTimestampFormat,
        ?Encryption $encryption,
        ?Signature $signature,
        array $dbConnectionTypeMapping
    ) {
        $this->basePath = $basePath;
        $this->filenameTemplate = $filenameTemplate;
        $this->filenameTimestampFormat = $filenameTimestampFormat;
        $this->encryption = $encryption ?? new Encryption();
        $this->signature = $signature ?? new Signature();
        $this->dbConnectionTypeMapping = $dbConnectionTypeMapping;
    }
}
