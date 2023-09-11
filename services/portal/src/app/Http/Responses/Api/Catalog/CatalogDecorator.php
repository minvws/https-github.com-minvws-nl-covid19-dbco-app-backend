<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Catalog;

use MinVWS\Codable\EncodableDecorator;

interface CatalogDecorator extends EncodableDecorator
{
    public const MODE_INDEX = 'index';
    public const MODE_SUMMARY = 'summary';
    public const MODE_FULL = 'full';

    public const PURPOSE = 'purpose';
    public const DIFF_TO_TYPE = 'diffToType';
    public const DIFF = 'diff';
}
