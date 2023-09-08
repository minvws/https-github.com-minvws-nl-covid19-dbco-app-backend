import { formatDate, isBetweenDays, parseDate } from '@/utils/date';
import { addDays, differenceInDays, endOfToday, isFuture, isSameDay, isValid, subDays } from 'date-fns';

interface DateFormatFilter {
    (value?: string | null | Date | number): string;
}

export function dateFnsFormat(value?: string | null | Date | number, format = 'dd-MM-yyyy') {
    if (!value) return '';

    return formatDate(value instanceof Date ? value : new Date(value), format);
}

export function dateFormatDeltaTime(value?: string | null, format: string | null = null) {
    if (!value) return '';

    const date = parseDate(value, format);
    const today = new Date();

    if (isSameDay(today, date)) {
        return 'Vandaag ';
    } else if (isSameDay(subDays(today, 1), date)) {
        return 'Gisteren ';
    } else if (isBetweenDays(date, subDays(today, 6), addDays(today, 1))) {
        return formatDate(date, 'EEEE') + ' ';
    }

    return formatDate(date, 'EEEE d MMM') + ' ';
}

/**
 * @example
 * dateFormatMonth('2020-12-23T12:30:00') // 23 dec. 2020
 */
export const dateFormatMonth: DateFormatFilter = (value) => dateFnsFormat(value, 'd MMM yyyy');

/**
 * @example
 * dateFormatMonth('2020-12-23T12:30:00') // 23 december 2020
 */
export const dateFormatMonthLong: DateFormatFilter = (value) => dateFnsFormat(value, 'd MMMM yyyy');

/**
 * @example
 * dateFormat('2020-12-23T12:30:00') // 23-12-2020
 */
export const dateFormat: DateFormatFilter = (value) => dateFnsFormat(value, 'dd-MM-yyyy');

/**
 * @example
 * dateFormatLong('2020-12-23T12:30:00') // woensdag 23 dec.
 */
export const dateFormatLong: DateFormatFilter = (value) => dateFnsFormat(value, 'EEEE d MMM');

/**
 * @example
 * dateTimeFormatLong('2020-12-23T12:30:00') // 23 dec. 2020 om 12:30
 */
export const dateTimeFormatLong: DateFormatFilter = (value) => dateFnsFormat(value, `dd MMM yyyy 'om' H:mm`);

/**
 * Returns string representative of the day IF within it falls within the last 72 hours, returns an empty string otherwise
 *
 * @example
 * dateLast3Days('...') // 'Eergisteren'
 * dateLast3Days('2020-12-23T12:30:00') // ''
 */
export const dateLast3Days: DateFormatFilter = (value) => {
    if (!value) return '';

    const date = value instanceof Date ? value : new Date(value);

    if (!isValid(date) || isFuture(date)) return '';

    const daysAgo = differenceInDays(endOfToday(), date);

    switch (daysAgo) {
        case 0:
            return 'Vandaag';
        case 1:
            return 'Gisteren';
        case 2:
            return 'Eergisteren';
        default:
            return '';
    }
};
