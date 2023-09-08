<?php

namespace MinVWS\Audit\Models;

use DateTimeImmutable;

/**
 * Represents an audit event.
 *
 * @package MinVWS\Audit\Models
 */
class AuditEvent
{
    public const ACTION_CREATE  = 'C';
    public const ACTION_READ    = 'R';
    public const ACTION_UPDATE  = 'U';
    public const ACTION_DELETE  = 'D';
    public const ACTION_EXECUTE = 'E';

    public const RESULT_SUCCESS      = 'success';
    public const RESULT_FORBIDDEN    = 'forbidden';
    public const RESULT_UNAUTHORIZED = 'unauthorized';
    public const RESULT_CLIENT_ERROR = 'clientError';
    public const RESULT_ERROR        = 'error';
    public const RESULT_UNKNOWN        = 'unknown';

    private string $code;
    private string $actionCode;
    private string $description;
    private DateTimeImmutable $createdAt;

    private ?string $result = null;
    private array $users = [];
    private array $objects = [];
    private ?string $service = null;

    /**
     * Constructor.
     *
     * @param string $code
     * @param string $actionCode
     * @param string $description
     * @todo Update the shared repository once we are integrating it with the DBCO portal.
     */
    private function __construct(string $code, string $actionCode, string $description)
    {
        $this->code = $code;
        $this->actionCode = $actionCode;
        $this->description = $description;
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * Creates a new audit event.
     *
     * @param string $code
     * @param string $actionCode
     * @param string $description
     *
     * @return static
     */
    public static function create(string $code, string $actionCode, string $description): self
    {
        return new self($code, $actionCode, $description);
    }

    /**
     * Allows overriding the automatically set timestamp for this event.
     *
     * @param DateTimeImmutable $createdAt
     *
     * @return $this
     */
    public function createdAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Modify the event code.
     *
     * @param string $code
     *
     * @return $this
     */
    public function code(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Modify the action code.
     *
     * @param string $actionCode
     *
     * @return $this
     */
    public function actionCode(string $actionCode): self
    {
        $this->actionCode = $actionCode;
        return $this;
    }

    /**
     * Modify the description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Sets the result.
     *
     * @param string $result
     *
     * @return $this
     */
    public function result(string $result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Add user.
     *
     * @param AuditUser $user
     *
     * @return $this
     */
    public function user(AuditUser $user): self
    {
        $this->users[] = $user;
        return $this;
    }

    /**
     * Register an object accessed.
     *
     * @param AuditObject $object
     *
     * @return $this
     */
    public function object(AuditObject $object): self
    {
        $this->objects[] = $object;
        return $this;
    }

    /**
     * Set the details of an AuditObject for a given $type
     *
     * @param string $type
     * @param string $key
     * @param mixed $details
     * @return $this
     */
    public function objectDetails(string $type, string $key, $details): self
    {
        foreach ($this->getObjects() as $object) {
            if ($object->getType() !== $type) {
                continue;
            }

            $object->detail($key, $details);
        }
        return $this;
    }

    /**
     * Register a bunch of objects (of the same type) accessed.
     *
     * @param AuditObject[] $objects
     *
     * @return $this
     */
    public function objects(array $objects): self
    {
        $this->objects = array_merge($this->objects, $objects);
        return $this;
    }

    /**
     * Sets the source service for this event.
     *
     * @param string $service
     *
     * @return $this
     */
    public function service(string $service): self
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Returns the event code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns the action code for this event.
     *
     * @return string
     */
    public function getActionCode(): string
    {
        return $this->actionCode;
    }

    /**
     * Returns the timestamp for this event.
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Returns the result for this event.
     *
     * @return string|null
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * Returns the objects for this event.
     *
     * @return AuditObject[]
     */
    public function getObjects(): array
    {
        return $this->objects;
    }

    /**
     * Returns the users for this event.
     *
     * @return array<AuditUser>
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * Returns the source service for this event.
     *
     * @return string|null
     */
    public function getService(): ?string
    {
        return $this->service;
    }

    /**
     * Returns the description for this event
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
