<?php

declare(strict_types=1);

namespace App\Ldap;

use function array_filter;
use function array_map;
use function array_merge;
use function array_shift;
use function assert;
use function count;
use function is_string;
use function preg_replace;
use function preg_split;
use function sprintf;
use function strtoupper;

class LdapDnParser
{
    public function extractAttribute(string $dnString, string $attributeName): ?string
    {
        $rdns = $this->splitUnescaped(',', $dnString);

        /*
         * List all attributes of all RDN's in a one-dimensional array
         * E.g. before: ['CN=John\, Doe+UID=123', 'C=NL', 'MAIL=foo@bar.com']
         *      after:  ['CN=John\, Doe', 'UID=123', 'C=NL', 'MAIL=foo@bar.com']
         */
        $attributes = array_merge(...array_filter(array_map(
            fn (string $rdnString) => $this->splitUnescaped('+', $rdnString),
            $rdns,
        )));

        $commonName = array_filter(array_map(
            function ($attribute) use ($attributeName) {
                $attributeParts = $this->splitUnescaped('=', $attribute);
                return count($attributeParts) === 2 && strtoupper($attributeParts[0]) === $attributeName
                    ? $this->escape($attributeParts[1])
                    : false;
            },
            $attributes,
        ));

        return count($commonName) === 1 ? array_shift($commonName) : null;
    }

    /**
     * @return array<int,string>
     */
    private function splitUnescaped(string $char, string $source): array
    {
        return preg_split(sprintf('#(?<!\\\\)\\%s#', $char), $source) ?: [];
    }

    private function escape(string $value): string
    {
        $value = preg_replace("#([\"\\\\=,+';<>])#", '\\\\$0', $value);
        assert(is_string($value));
        $value = preg_replace('/^([ #])/', '\\\\$0', $value);
        assert(is_string($value));
        $value = preg_replace('/ $/', '\\\\ ', $value);
        assert(is_string($value));

        return $value;
    }
}
