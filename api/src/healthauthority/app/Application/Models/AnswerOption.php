<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Answer option.
 */
class AnswerOption
{
    /**
     * @var string
     */
    public string $label;

    /**
     * @var string
     */
    public string $value;

    /**
     * @var string
     */
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
        $this->label = $label;
        $this->value = $value;
        $this->trigger = $trigger;
    }
}
