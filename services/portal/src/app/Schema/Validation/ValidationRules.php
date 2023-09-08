<?php

declare(strict_types=1);

namespace App\Schema\Validation;

use Closure;

use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function is_array;
use function is_string;
use function str_replace;

class ValidationRules
{
    public const ALL = 'all';
    public const FATAL = 'fatal';
    public const WARNING = 'warning';
    public const NOTICE = 'notice';
    public const OSIRIS_APPROVED = 'osiris_approved';
    public const OSIRIS_FINISHED = 'osiris_finished';

    private array $rules = [];
    private array $children = [];
    private bool $enabled = true;

    /**
     * Factory method to create a new validation rules container for the given rules
     * with the given validation level.
     */
    public static function create(array $rules = [], string $level = self::FATAL): self
    {
        $result = new self();
        foreach ($rules as $rule) {
            $result->addRule($rule, $level);
        }

        return $result;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Add fatal rule (compatible with Laravel validation).
     *
     * If a closure has been given the closure will be called with the validation context
     * as argument shortly before validation.
     */
    public function addFatal(mixed $rule, array $tags = []): self
    {
        return $this->addRule($rule, self::FATAL, $tags);
    }

    public function removeFatal(mixed $rule): self
    {
        return $this->removeRule($rule, self::FATAL);
    }

    /**
     * Add warning rule (compatible with Laravel validation).
     *
     * If a closure has been given the closure will be called with the validation context
     * as argument shortly before validation.
     */
    public function addWarning(mixed $rule, array $tags = []): self
    {
        return $this->addRule($rule, self::WARNING, $tags);
    }

    public function removeWarning(mixed $rule): self
    {
        return $this->removeRule($rule, self::WARNING);
    }

    /**
     * Add notice rule (compatible with Laravel validation).
     *
     * If a closure has been given the closure will be called with the validation context
     * as argument shortly before validation.
     */
    public function addNotice(mixed $rule, array $tags = []): self
    {
        return $this->addRule($rule, self::NOTICE, $tags);
    }

    public function removeNotice(mixed $rule): self
    {
        return $this->removeRule($rule, self::NOTICE);
    }

    /**
     * @deprecated This is Osiris specific and should probably be replaced with the warning levels.
     *             Will be fixed with ticket https://egeniq.atlassian.net/browse/DBCO-2272
     *
     * Add 'osiris approved' rule (compatible with Laravel validation).
     *
     * If a closure has been given the closure will be called with the validation context
     * as argument shortly before validation.
     */
    public function addOsirisApproved(mixed $rule): self
    {
        return $this->addRule($rule, self::OSIRIS_APPROVED);
    }

    /**
     * @deprecated This is Osiris specific and should probably be replaced with the warning levels.
     *             Will be fixed with ticket https://egeniq.atlassian.net/browse/DBCO-2272
     *
     * Removes the given 'osiris approved' rule.
     */
    public function removeOsirisApproved(mixed $rule): self
    {
        return $this->removeRule($rule, self::OSIRIS_APPROVED);
    }

    /**
     * @deprecated This is Osiris specific and should probably be replaced with the warning levels.
     *             Will be fixed with ticket https://egeniq.atlassian.net/browse/DBCO-2272
     *
     * Add 'osiris finished' rule (compatible with Laravel validation).
     *
     * If a closure has been given the closure will be called with the validation context
     * as argument shortly before validation.
     */
    public function addOsirisFinished(mixed $rule): self
    {
        return $this->addRule($rule, self::OSIRIS_FINISHED);
    }

    /**
     * @deprecated This is Osiris specific and should probably be replaced with the warning levels.
     *             Will be fixed with ticket https://egeniq.atlassian.net/browse/DBCO-2272
     */
    public function removeOsirisFinished(mixed $rule): self
    {
        return $this->removeRule($rule, self::OSIRIS_FINISHED);
    }

    /**
     * Add rule for the given level (compatible with Laravel validation).
     *
     * If a closure has been given the closure will be called with the validation context
     * as argument shortly before validation.
     */
    public function addRule(mixed $rule, string $level = self::ALL, array $tags = []): self
    {
        $this->rules[$level][] = ValidationRule::create($rule, $tags);
        return $this;
    }

    /**
     * Removes the given rule at the given level.
     */
    public function removeRule(mixed $rule, string $level = self::ALL): self
    {
        $levels = [$level];
        if ($level === self::ALL) {
            $levels = array_keys($this->rules);
        }

        foreach ($levels as $level) {
            $this->rules[$level] = array_filter($this->rules[$level], static fn($r) => $r->rule !== $rule);
        }

        return $this;
    }

    /**
     * Add child validation container.
     *
     * @param ValidationRules $child Child rules container.
     * @param string|null $key (Optional) key to add to path.
     */
    public function addChild(ValidationRules $child, ?string $key = null): self
    {
        $this->children[] = ['child' => $child, 'key' => $key];
        return $this;
    }

    /**
     * Flattens the validation rules to an array of ValidationRule objects. To make the array compatible with Laravel
     * validation, you should use the ValidationTagging trait (mapRules).
     */
    public function make(ValidationContext $context): array
    {
        $rules = [];

        $this->makeChildren($context, $rules);

        return $rules;
    }

    /**
     * Returns the rules for the given level.
     */
    protected function getRulesForLevel(string $level, ValidationContext $context): array
    {
        $rawRules = $this->rules[$level] ?? [];

        $rules = [];
        foreach ($rawRules as $rule) {
            if ($rule->rule instanceof Closure) {
                $ruleClosure = $rule->rule;
                $rule = ValidationRule::create($ruleClosure($context), $rule->tags);
            }

            if ($rule->rule === null) {
                continue;
            }

            if (is_array($rule->rule)) {
                $rules = array_merge($rules, array_map(static function ($r) use ($rule) {
                    return ValidationRule::create($r, $rule->tags);
                }, $rule->rule));
            } else {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Replace prefix path in rules.
     */
    protected function resolvePrefixPathForRules(array $rules, ValidationContext $context): array
    {
        return array_map(
            static function ($validationRule) use ($context) {
                if (is_string($validationRule->rule)) {
                    $validationRule->rule = str_replace('{PREFIX_PATH}', $context->getPrefixPath(), $validationRule->rule);
                }

                return $validationRule;
            },
            $rules,
        );
    }

    /**
     * Fill rules array with the rules at the current path.
     */
    protected function makeRules(ValidationContext $context, array &$rules): void
    {
        if (!$this->isEnabled() || empty($context->getPath())) {
            return;
        }

        $rules[$context->getPath()] ??= [];

        $levels = $context->getLevel() === self::ALL ? [self::ALL] : [self::ALL, $context->getLevel()];

        foreach ($levels as $level) {
            $rulesForLevel = $this->getRulesForLevel($level, $context);
            $rulesForLevel = $this->resolvePrefixPathForRules($rulesForLevel, $context);

            $rules[$context->getPath()] = array_merge(
                $rules[$context->getPath()],
                $rulesForLevel,
            );
        }

        if (count($rules[$context->getPath()]) === 0) {
            unset($rules[$context->getPath()]);
        }
    }

    /**
     * Fill rules with child rules.
     */
    protected function makeChildren(ValidationContext $context, array &$rules): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        foreach ($this->children as $childData) {
            $child = $childData['child'];
            $key = $childData['key'];

            $childContext = empty($key) ? $context : $context->nestedContext($key);
            $child->makeRules($childContext, $rules);
            $child->makeChildren($childContext, $rules);
        }
    }
}
