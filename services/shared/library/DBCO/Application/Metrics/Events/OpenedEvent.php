<?php
namespace DBCO\Shared\Application\Metrics\Events;

/**
 * Opened event.
 */
class OpenedEvent extends AbstractEvent
{
    /**
     * Create opened event.
     *
     * @param string $actor
     * @param string $caseUuid
     */
    public function __construct(string $actor, string $caseUuid)
    {
        parent::__construct('opened', $actor, $caseUuid);
    }

    /**
     * Create paired event.
     *
     * @param string $actor
     * @param string $caseUuid
     *
     * @return static
     */
    public static function paired(string $actor, string $caseUuid): self
    {
        return new self(self::TYPE_PAIRED, $actor, $caseUuid);
    }

    /**
     * Create expired event.
     *
     * @param string $actor
     * @param string $caseUuid
     *
     * @return static
     */
    public static function expired(string $actor, string $caseUuid): self
    {
        return new self(self::TYPE_EXPIRED, $actor, $caseUuid);
    }

    /**
     * Create identified event.
     *
     * @param string $actor
     * @param string $caseUuid
     * @param string $taskUuid
     *
     * @return static
     */
    public static function identified(string $actor, string $caseUuid, string $taskUuid): self
    {
        return new self(self::TYPE_IDENTIFIED, $actor, $caseUuid, $taskUuid);
    }

    /**
     * Create inventoried event.
     *
     * @param string $actor
     * @param string $caseUuid
     * @param string $taskUuid
     *
     * @return static
     */
    public static function inventoried(string $actor, string $caseUuid, string $taskUuid): self
    {
        return new self(self::TYPE_INVENTORIED, $actor, $caseUuid, $taskUuid);
    }

    /**
     * Create edited event.
     *
     * @param string   $actor
     * @param string   $caseUuid
     * @param string   $taskUuid
     * @param string[] $taskFields
     *
     * @return static
     */
    public static function edited(string $actor, string $caseUuid, string $taskUuid, array $taskFields): self
    {
        return new self(self::TYPE_EDITED, $actor, $caseUuid, $taskUuid, $taskFields);
    }

    /**
     * Create exported event.
     *
     * @param string $actor
     * @param string $caseUuid
     * @param string $taskUuid
     *
     * @return static
     */
    public static function exported(string $actor, string $caseUuid, string $taskUuid): self
    {
        return new self(self::TYPE_EXPORTED, $actor, $caseUuid, $taskUuid);
    }
}