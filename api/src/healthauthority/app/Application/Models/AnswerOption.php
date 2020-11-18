<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

use Ramsey\Uuid\Nonstandard\Uuid;

/**
 * Answer option.
 */
class AnswerOption
{
    public string $uuid;

    public string $label;

    public string $value;

    public ?string $trigger;

    /**
     * Constructor.
     *
     * @param string      $label
     * @param string      $value
     * @param string|null $trigger
     */
    public function __construct(string $label, string $value, ?string $trigger = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->label = $label;
        $this->value = $value;
        $this->trigger = $trigger;
    }
}
