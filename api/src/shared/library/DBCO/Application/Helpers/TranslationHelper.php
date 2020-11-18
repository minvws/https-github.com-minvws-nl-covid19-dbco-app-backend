<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Helpers;

/**
 * Utility methods for helping with translations.
 *
 * @package DBCO\Shared\Application\Helpers
 */
class TranslationHelper
{
    /**
     * Extracts the first matching language we support based on the given
     * Accept-Language header.
     *
     * @param array $acceptLanguage
     *
     * @return string
     */
    public function getLanguageForAcceptLanguageHeader(array $acceptLanguage): string
    {
        // TODO
        return 'nl_NL';
    }
}
