<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;

use function preg_match;

class RefererService
{
    private const PATTERN_COVID_CASE_OVERVIEW_PLANNER_PAGE = '/\/planner/i';

    public static function originatesFromCovidCaseOverviewPlannerPage(Request $request): bool
    {
        return (bool) preg_match(self::PATTERN_COVID_CASE_OVERVIEW_PLANNER_PAGE, $request->headers->get('referer') ?? '');
    }
}
