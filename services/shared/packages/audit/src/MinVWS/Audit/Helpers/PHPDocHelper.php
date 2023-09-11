<?php

declare(strict_types=1);

namespace MinVWS\Audit\Helpers;

use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;

/**
 * @deprecated Use PHP Annotations instead. See \MinVWS\Audit\Helpers\AuditEventHelper.
 */
class PHPDocHelper
{
    public const TAG_AUDIT_EVENT_DESCRIPTION = 'auditEventDescription';

    /**
     * Get function description by action name
     */
    public static function getTagDescriptionByActionName(string $tag, string $action): string
    {
        $actionParts = self::getActionParts($action);

        if (count($actionParts) == 2 && method_exists($actionParts[0], $actionParts[1])) {
            $method = new ReflectionMethod($actionParts[0], $actionParts[1]);
            $factory = DocBlockFactory::createInstance();
            $docComment = $method->getDocComment();

            if (is_string($docComment)) {
                $docblock = $factory->create($docComment);

                if (!empty($docblock->getTagsByName($tag)[0])) {
                    return $docblock->getTagsByName($tag)[0]->getDescription()->render();
                }
            }
        }

        return '';
    }

    /**
     * Get tag auditEventDescription
     *
     * @param string $action
     * @return string
     */
    public static function getTagAuditEventDescriptionByActionName(string $action): string
    {
        return self::getTagDescriptionByActionName(self::TAG_AUDIT_EVENT_DESCRIPTION, $action);
    }

    /**
     * Get action parts
     *
     * @param $action
     * @return array
     */
    public static function getActionParts($action): array
    {
        $parts = explode('@', $action);
        if (count($parts) != 2) {
            $parts = explode('::', $action);
        }

        return $parts;
    }
}
