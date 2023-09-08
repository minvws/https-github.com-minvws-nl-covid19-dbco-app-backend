<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\PlannerCase\PlannerView;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function config;

class CaseHelper
{
    /**
     * Determine the enddate of the contagious period of the index
     */
    public static function endOfContagiousPeriodIndex(
        EloquentCase $covidCase,
        YesNoUnknown $hasUnderlyingSufferingOrMedication,
        YesNoUnknown $isImmunoCompromised,
    ): CarbonInterface {
        if ($covidCase->date_of_test === null) {
            return CarbonImmutable::now();
        }

        if (isset($covidCase->symptomatic) && !$covidCase->symptomatic) {
            return $covidCase->date_of_test->addDays(config('misc.case.contagiousPeriodWhenNotSymptomaticInDays'));
        }

        if ($hasUnderlyingSufferingOrMedication === YesNoUnknown::yes() && $isImmunoCompromised === YesNoUnknown::yes()) {
            return $covidCase->date_of_test->addDays(config('misc.case.contagiousPeriodWhenImmonoCompromisedInDays'));
        }

        if ($covidCase->date_of_symptom_onset === null) {
            return CarbonImmutable::now();
        }

        return $covidCase->date_of_symptom_onset->addDays(config('misc.case.contagiousPeriodInDays'));
    }

    /**
     * Determine the PlannerView value for the given case. The order in which the checks are done, also determine
     * the outcome of the return result.
     */
    public static function getPlannerView(EloquentCase $case, string $requiredSelectedOrganisation): PlannerView
    {
        // Assigned to other organisation (might also be assigned to a caselist of other organisation)
        if (
            $case->assigned_organisation_uuid &&
            $case->assigned_organisation_uuid !== $requiredSelectedOrganisation
        ) {
            return PlannerView::outsourced();
        }

        // BCOStatus archived is always archived
        if ($case->bcoStatus === BCOStatus::archived()) {
            return PlannerView::archived();
        }

        // Enter if statement when BCOStatus is completed
        if ($case->bcoStatus === BCOStatus::completed()) {
            if ($case->assigned_user_uuid && $case->organisation_uuid === $requiredSelectedOrganisation) {
                // If the case is assigned, return it as assigned
                return PlannerView::assigned();
            }

            // else we should check if it is approved or not
            return $case->isApproved === false ? PlannerView::unassigned() : PlannerView::completed();
        }

        if ($case->bcoStatus !== BCOStatus::draft() && $case->bcoStatus !== BCOStatus::open()) {
            return PlannerView::unknown(); //Should not happen
        }

        //Assigned to my organisation
        if (
            $case->assigned_organisation_uuid &&
            empty($case->assigned_case_list_uuid) &&
            $case->assigned_organisation_uuid === $requiredSelectedOrganisation
        ) {
            return $case->assigned_user_uuid ? PlannerView::assigned() : PlannerView::unassigned();
        }

        //Assigned to a user from my organisation
        if (
            $case->assigned_user_uuid &&
            empty($case->assigned_case_list_uuid) &&
            $case->organisation_uuid === $requiredSelectedOrganisation
        ) {
            return PlannerView::assigned();
        }

        //Assigned to a caselist from my organisation
        if ($case->assignedCaseList) {
            if ($case->assigned_user_uuid) {
                return PlannerView::assigned();
            }
            return $case->assignedCaseList->is_queue ? PlannerView::queued() : PlannerView::unassigned();
        }

        if (empty($case->assigned_user_uuid) && empty($case->assigned_organisation_uuid) && empty($case->assigned_case_list_uuid)) {
            return PlannerView::unassigned();
        }

        return PlannerView::unknown();
    }

    public static function isCaseAccessibleByOrganisation(
        EloquentCase $eloquentCase,
        EloquentOrganisation $organisation,
    ): bool {
        if ($eloquentCase->organisation_uuid === $organisation->uuid) {
            return true;
        }

        return $eloquentCase->assigned_organisation_uuid === $organisation->uuid;
    }

    public static function isCaseUntouched(EloquentCase $eloquentCase): bool
    {
        if ($eloquentCase->assigned_organisation_uuid !== null) {
            return false;
        }

        if ($eloquentCase->assigned_user_uuid !== null) {
            return false;
        }

        return $eloquentCase->bcoStatus === BCOStatus::draft();
    }

    public static function isInEditWindow(EloquentCase $eloquentCase): bool
    {
        if ($eloquentCase->created_at === null) {
            return false;
        }

        return $eloquentCase->created_at->diffInSeconds() < config('misc.case.ownerNewCaseEditIntervalInSeconds');
    }
}
