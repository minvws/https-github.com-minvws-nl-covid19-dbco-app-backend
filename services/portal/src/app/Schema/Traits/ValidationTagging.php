<?php

declare(strict_types=1);

namespace App\Schema\Traits;

use App\Schema\Validation\ValidationRule;

use function array_filter;
use function array_intersect;
use function array_map;
use function count;
use function in_array;
use function is_array;

trait ValidationTagging
{
    /**
     * Map an array of ValidationRule objects to an array compatible with Laravel validation.
     *
     * @param array $filterTags Optional list of tags to filter the rules on
     */
    public function mapRules(array $rules, array $filterTags = []): array
    {
        foreach ($rules as $severityLevel => $levelRules) {
            $rules[$severityLevel] = array_map(function ($levelRule) use ($filterTags) {
                if (is_array($levelRule)) {
                    return array_map(static function ($rule) {
                        if ($rule instanceof ValidationRule) {
                            return $rule->rule;
                        }
                        return $rule;
                    }, $this->filterByRuleTags($levelRule, $filterTags));
                }
                if ($levelRule instanceof ValidationRule) {
                    return $levelRule->rule;
                }
                return $levelRule;
            }, $this->filterByRuleTags($levelRules, $filterTags));
        }

        return $rules;
    }

    private function filterByRuleTags(array $rules, array $filterTags): array
    {
        if (empty($filterTags)) {
            return $rules;
        }

        return array_filter($rules, static function ($filterRule) use ($filterTags) {
            if (!$filterRule instanceof ValidationRule) {
                return true;
            }

            if (empty($filterRule->tags)) {
                return false;
            }

            if (in_array(ValidationRule::TAG_ALWAYS, $filterRule->tags, true)) {
                return true;
            }

            return count(array_intersect($filterRule->tags, $filterTags)) > 0;
        });
    }
}
