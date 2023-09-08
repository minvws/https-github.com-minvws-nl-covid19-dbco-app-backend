<?php

declare(strict_types=1);

namespace MinVWS\Audit\Helpers;

use MinVWS\Audit\Attribute\SetAuditEventCode;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use ReflectionMethod;
use RuntimeException;

use function array_pop;
use function count;
use function explode;
use function method_exists;
use function sprintf;
use function str_replace;

class AuditEventHelper
{
    public const ATTRIBUTE_AUDIT_EVENT_DESCRIPTION = SetAuditEventDescription::class;
    public const ATTRIBUTE_AUDIT_EVENT_CODE = SetAuditEventCode::class;

    /**
     * @template T of object
     *
     * @param class-string<T>|null $attributeFilter
     *
     * @return array<ReflectionAttribute<T>>
     */
    public static function getAttributesByActionName(string $action, ?string $attributeFilter = null): array
    {
        [$class, $method] = self::getActionparts($action);
        if (!method_exists($class, $method)) {
            return [];
        }

        return (new ReflectionMethod($class, $method))->getAttributes($attributeFilter);
    }

    public static function getAuditEventDescriptionByActionName(string $action): string
    {
        $attributes = self::getAttributesByActionName($action, self::ATTRIBUTE_AUDIT_EVENT_DESCRIPTION);

        return array_pop($attributes)?->newInstance()->description ?? '';
    }

    public static function getAuditEventCodeByActionName(string $action): string
    {
        $attributes = self::getAttributesByActionName($action, self::ATTRIBUTE_AUDIT_EVENT_CODE);

        return array_pop($attributes)?->newInstance()->code ?? '';
    }

    /**
     * @return non-empty-array{0: string, 1: string}
     */
    public static function getActionparts(string $action): array
    {
        /** @var array{0: string, 1?: string} */
        $parts = explode('@', str_replace('::', '@', $action), 2);

        if (count($parts) !== 2) {
            throw new RuntimeException(sprintf('Invalid action format provided: "%s"', $action));
        }

        return $parts;
    }
}
