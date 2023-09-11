<?php

declare(strict_types=1);

namespace App\Models;

use function in_array;

final class StatusIndexContactTracing
{
    public string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function isCompleted(): bool
    {
        return in_array($this->value, [
            self::COMPLETED()->value,
            self::FOUR_TIMES_NOT_REACHED()->value,
            self::BCO_FINISHED()->value,
        ], true);
    }

    public function isClosed(): bool
    {
        return in_array($this->value, [
            self::CLOSED_OUTSIDE_GGD()->value,
            self::CLOSED_NO_COLLABORATION()->value,
            self::CLOSED()->value,
        ], true);
    }

    public function isOpen(): bool
    {
        return in_array($this->value, [
            self::NOT_APPROACHED()->value,
            self::NOT_REACHABLE()->value,
            self::CONVERSATION_STARTED()->value,
            self::NEW()->value,
            self::NOT_STARTED()->value,
            self::TWO_TIMES_NOT_REACHED()->value,
            self::CALLBACK_REQUEST()->value,
            self::LOOSE_END()->value,
        ], true);
    }

    public function requiresExplanation(): bool
    {
        return in_array($this->value, [
            self::TWO_TIMES_NOT_REACHED()->value,
            self::CALLBACK_REQUEST()->value,
            self::LOOSE_END()->value,
        ], true);
    }

    /**
     * @return array<StatusIndexContactTracing>
     */
    public static function all(): array
    {
        return [
            self::NOT_APPROACHED(),
            self::NOT_REACHABLE(),
            self::CONVERSATION_STARTED(),
            self::CLOSED_OUTSIDE_GGD(),
            self::CLOSED_NO_COLLABORATION(),
            self::COMPLETED(),
            self::UNKNOWN(),
            self::NEW(),
            self::NOT_STARTED(),
            self::TWO_TIMES_NOT_REACHED(),
            self::CALLBACK_REQUEST(),
            self::LOOSE_END(),
            self::FOUR_TIMES_NOT_REACHED(),
            self::BCO_FINISHED(),
            self::CLOSED(),
        ];
    }

    public static function fromString(string $value): StatusIndexContactTracing
    {
        switch ($value) {
            case "not_approached":
            case "not_reachable":
            case "conversation_started":
            case "closed_outside_ggd":
            case "closed_no_collaboration":
            case "completed":
            case "new":
            case "not_started":
            case "two_times_not_reached":
            case "callback_request":
            case "loose_end":
            case "four_times_not_reached":
            case "bco_finished":
            case "closed":
                return new StatusIndexContactTracing($value);
            default:
                return self::UNKNOWN();
        }
    }

    public static function defaultItem(): self
    {
        return self::NEW();
    }

    public static function UNKNOWN(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("unknown");
    }

    public static function NOT_APPROACHED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("not_approached");
    }

    public static function NOT_REACHABLE(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("not_reachable");
    }

    public static function CONVERSATION_STARTED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("conversation_started");
    }

    public static function CLOSED_OUTSIDE_GGD(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("closed_outside_ggd");
    }

    public static function CLOSED_NO_COLLABORATION(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("closed_no_collaboration");
    }

    public static function COMPLETED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("completed");
    }

    public static function NEW(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("new");
    }

    public static function NOT_STARTED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("not_started");
    }

    public static function TWO_TIMES_NOT_REACHED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("two_times_not_reached");
    }

    public static function CALLBACK_REQUEST(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("callback_request");
    }

    public static function LOOSE_END(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("loose_end");
    }

    public static function FOUR_TIMES_NOT_REACHED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("four_times_not_reached");
    }

    public static function BCO_FINISHED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("bco_finished");
    }

    public static function CLOSED(): StatusIndexContactTracing
    {
        return new StatusIndexContactTracing("closed");
    }
}
