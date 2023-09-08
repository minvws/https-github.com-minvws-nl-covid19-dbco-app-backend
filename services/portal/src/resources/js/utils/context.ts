import { CaseFilterKey, ContextGroup } from '@/components/form/ts/formTypes';
import { isAfter, isBefore, startOfDay } from 'date-fns';
import { isBetweenDays } from './date';
import type { PlaceCasesResponse } from '@dbco/portal-api/place.dto';
import { useAssignmentStore } from '@/store/assignment/assignmentStore';
import { YesNoUnknownV1 } from '@dbco/enum';
import type { Range } from './caseDateRanges';

export enum SourceAndContagiousDateClassification {
    BEFORE_SOURCE = 'BEFORE_SOURCE',
    SOURCE = 'SOURCE',
    SOURCE_AND_CONTAGIOUS = 'SOURCE_AND_CONTAGIOUS',
    CONTAGIOUS = 'CONTAGIOUS',
    AFTER_CONTAGIOUS = 'AFTER_CONTAGIOUS',
    UNKNOWN = 'UNKNOWN',
}

/**
 * Classifies given dates into classifications defined in SourceAndContagiousDateClassification,
 * based on the source and contagious periods of the case.
 */
export const classifyDates = (
    dates: Date[],
    sourcePeriod: Range | null,
    contagiousPeriod: Range | null
): SourceAndContagiousDateClassification[] => {
    const adjustedDates = dates.map(startOfDay);
    let sourceAndContagiousDates: Range;

    // Find period where both source and contagious dates overlap
    if (sourcePeriod && contagiousPeriod && sourcePeriod.endDate >= contagiousPeriod.startDate) {
        sourceAndContagiousDates = {
            key: CaseFilterKey.QuarantineEnd,
            startDate: contagiousPeriod.startDate,
            endDate: sourcePeriod.endDate,
        };
    }

    return adjustedDates.map((date) => {
        if (
            sourceAndContagiousDates &&
            isBetweenDays(date, sourceAndContagiousDates.startDate, sourceAndContagiousDates.endDate, '[]')
        ) {
            return SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS;
        }

        if (sourcePeriod) {
            if (isBefore(date, sourcePeriod.startDate)) {
                return SourceAndContagiousDateClassification.BEFORE_SOURCE;
            } else if (isBetweenDays(date, sourcePeriod.startDate, sourcePeriod.endDate, '[]')) {
                return SourceAndContagiousDateClassification.SOURCE;
            }
        }

        if (contagiousPeriod) {
            if (isAfter(date, contagiousPeriod.endDate)) {
                return SourceAndContagiousDateClassification.AFTER_CONTAGIOUS;
            } else if (isBetweenDays(date, contagiousPeriod.startDate, contagiousPeriod.endDate, '[]')) {
                return SourceAndContagiousDateClassification.CONTAGIOUS;
            }
        }

        return SourceAndContagiousDateClassification.UNKNOWN;
    });
};

// Return a warning message if every date classifications is before the source OR after the contagious period
export const getClassificationWarning = (
    classifications: SourceAndContagiousDateClassification[]
): string | undefined => {
    if (classifications.length === 0) return;

    if (classifications.every((x) => x === SourceAndContagiousDateClassification.BEFORE_SOURCE)) {
        return 'Het laatste bezoek was vóór de bronperiode. Deze context hoeft niet te worden opgenomen in het dossier.';
    } else if (classifications.every((x) => x === SourceAndContagiousDateClassification.AFTER_CONTAGIOUS)) {
        return 'Het laatste bezoek was na de besmettelijke periode. Deze context hoeft niet te worden opgenomen in het dossier.';
    }
};

// Checks if ALL given date classifications are part of the other context group (no overlapping allowed)
export const areAllDatesInOtherContextGroup = (
    classifications: SourceAndContagiousDateClassification[],
    group: ContextGroup
): boolean => {
    if (classifications.length === 0) return false;

    if (classifications.some((x) => x === SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS)) return false;

    if (group === ContextGroup.Source) {
        return classifications.every((x) =>
            [
                SourceAndContagiousDateClassification.CONTAGIOUS,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
            ].includes(x)
        );
    }

    if (group === ContextGroup.Contagious) {
        return classifications.every((x) =>
            [
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.SOURCE,
            ].includes(x)
        );
    }

    return false;
};

// Checks if SOME given date classifications are part of the given context group
export const areDatesInContextGroup = (
    classifications: SourceAndContagiousDateClassification[],
    group: ContextGroup
): boolean => {
    if (classifications.length === 0) return false;

    if (
        group === ContextGroup.All ||
        classifications.every((x) => x === SourceAndContagiousDateClassification.UNKNOWN)
    ) {
        return true;
    }

    if (group === ContextGroup.Source) {
        return classifications.some((x) =>
            [
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.SOURCE,
                SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS,
            ].includes(x)
        );
    }

    if (group === ContextGroup.Contagious) {
        return classifications.some((x) =>
            [
                SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS,
                SourceAndContagiousDateClassification.CONTAGIOUS,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
            ].includes(x)
        );
    }

    return false;
};

export const formattedName = ({
    notificationNamedConsent,
    name,
}: Pick<PlaceCasesResponse, 'notificationNamedConsent' | 'name'>) => {
    if (!notificationNamedConsent) return '-';
    return name || '-';
};

export const rowClicked = async (uuid: string, token: string) => {
    const assignmentStore = useAssignmentStore();
    try {
        await assignmentStore.getAccessToCase({ uuid, token });
        window.open(`/editcase/${uuid}`, '_blank');
    } catch (error) {
        /* empty */
    }
};

export const showTombstone = (isDeceased: PlaceCasesResponse['isDeceased']) => isDeceased === YesNoUnknownV1.VALUE_yes;
export const showExclamationBubble = (causeForConcern: PlaceCasesResponse['causeForConcern']) =>
    causeForConcern === YesNoUnknownV1.VALUE_yes;
