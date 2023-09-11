import {
    add,
    compareAsc,
    formatDistanceToNow,
    isAfter,
    isBefore,
    isSameDay,
    isValid,
    nextMonday,
    parse,
    parseJSON,
    setDay,
    startOfDay,
    startOfMonth,
    sub,
    subDays,
} from 'date-fns';
import dateFnsFormat from 'date-fns/format';
import differenceInCalendarDays from 'date-fns/differenceInCalendarDays';
import differenceInYears from 'date-fns/differenceInYears';
import differenceInDays from 'date-fns/differenceInDays';
import { nl } from 'date-fns/locale';
import { formatInTimeZone } from 'date-fns-tz';

export const ensureDate = (date: string | Date) => (date instanceof Date ? date : new Date(date));

export const includesDay = (dates: (string | Date)[], target: string | Date) =>
    dates.some((x) => isSameDay(ensureDate(x), ensureDate(target)));

export const parseDate = (
    dateString: string,
    formatString: string | null = null,
    referenceDate: number | Date = new Date(),
    options?: Record<string, unknown>
): Date => {
    let date = null;
    if (formatString) {
        date = parse(dateString, formatString, referenceDate, {
            locale: nl,
            ...options,
        });
    } else {
        date = parseJSON(dateString);
    }

    return isValid(date) ? date : new Date();
};

export const formatDate: typeof dateFnsFormat = (date, format, options) => {
    return dateFnsFormat(date, format, {
        locale: nl,
        ...options,
    });
};

export const formatFromNow: typeof formatDistanceToNow = (date, options) => {
    return formatDistanceToNow(date, {
        locale: nl,
        ...options,
    });
};

export const formatInOtherTimeZone: typeof formatInTimeZone = (date, timeZone, format, options) =>
    formatInTimeZone(date, timeZone, format, { locale: nl, ...options });

export const getDifferenceInDays: typeof differenceInDays = (
    dateLeft: Date | number,
    dateRight: Date | number
): number => {
    return differenceInDays(startOfDay(dateLeft), startOfDay(dateRight));
};

export const isBetweenDays = (date: Date, from: Date, to: Date, inclusivity = '()') => {
    if (!['()', '[]', '(]', '[)'].includes(inclusivity)) {
        throw new Error('Inclusivity parameter must be one of (), [], (], [)');
    }

    date = startOfDay(date);
    from = startOfDay(from);
    to = startOfDay(to);

    const isBeforeEqual = inclusivity[0] === '[',
        isAfterEqual = inclusivity[1] === ']';

    return (
        (isBeforeEqual ? isSameDay(from, date) || isBefore(from, date) : isBefore(from, date)) &&
        (isAfterEqual ? isSameDay(to, date) || isAfter(to, date) : isAfter(to, date))
    );
};

export const calculateAge = (date: Date): number => {
    return differenceInYears(new Date(), date);
};

/**
 * Returns the monday of the week (week starts on monday)
 *
 * @param {Date} date
 * @returns {Date}
 */
export const getMonday = (date: Date) => {
    return nextMonday(subDays(date, 7));
};

export const formatDateLong = (date: Date) => formatDate(date, 'EEEE d MMM');

// formats '7', '7:00', '7.00', '07.00' into '07:00', or '0711' into '07:11'
export const formatTime = (time: string | null) => {
    // Return input if falsy or not [digits . :]
    if (!time || !time.match(/[\d.:]/)) return time;
    let hours = '';
    let minutes = '';
    if (time.includes(':')) {
        [hours, minutes] = time.split(':');
    } else if (time.includes('.')) {
        [hours, minutes] = time.split('.');
    } else if (time.length <= 2) {
        // Assume hours only
        hours = time;
    } else {
        // Converts 900 to 0900
        time = time.padStart(4, '0');

        hours = time.slice(0, 2);
        minutes = time.slice(2, 4);
    }

    return `${hours.substr(0, 2).padStart(2, '0')}:${minutes.substr(0, 2).padStart(2, '0')}`;
};

/**
 * gets a string based on the two dates passed, originaly split from FormDateDifferenceLabel.vue
 * Might require additional refactoring for expanded use cases.
 * @param {Date} [dateOne] first date in the comparision
 * @param {Date} [dateTwo] seconde date in the comparison
 * @param {string} [dateTwoLabel] a label associated with date Two, potentially a relic from FormDateDifferenceLabel.vue where this function originated
 * @returns {string} a label based on the difference in weeks and days between the two dates
 */
export const getDifferenceStringForTwoDates = (
    dateOne: Date | null | undefined,
    dateTwo: Date | null | undefined,
    dateTwoLabel: string | null | undefined
): string => {
    if (!dateOne || !dateTwo) return '';

    const diff = differenceInCalendarDays(dateOne, dateTwo);

    if (diff === 0) {
        return dateTwoLabel ? `gelijk aan ${dateTwoLabel}` : 'vandaag';
    }

    const weeks = Math.floor(Math.abs(diff) / 7);
    const days = Math.abs(diff) - weeks * 7;

    let diffString = '';
    if (weeks === 1) {
        diffString += '1 week';
    }
    if (weeks > 1) {
        diffString += `${weeks} weken`;
    }
    if (weeks > 0 && days > 0) {
        diffString += ' en ';
    }
    if (days === 1) {
        diffString += '1 dag';
    }
    if (days > 1) {
        diffString += `${days} dagen`;
    }

    if (dateTwoLabel) {
        diffString += ` ${diff < 0 ? 'voor' : 'na'} ${dateTwoLabel}`;
    } else {
        diffString += ' geleden';
    }

    return diffString.trim();
};

export const getDayNames = () => [...Array(7).keys()].map((day) => formatDate(setDay(new Date(), day), 'EEEE'));

export const getMonthNames = () =>
    [...Array(12).keys()].map((month) => formatDate(startOfMonth(new Date()).setMonth(month), 'MMMM'));

export const getMonthNamesShort = () =>
    [...Array(12).keys()].map((month) => formatDate(startOfMonth(new Date()).setMonth(month), 'MMM'));

export const isAdjacentDate = (d1: Date, d2: Date) => {
    const nextDay = add(d1, { days: 1 });
    const previousDay = sub(d1, { days: 1 });
    return isSameDay(d2, nextDay) || isSameDay(d2, previousDay);
};

export const getSummaryTextForDates = (dates: (string | Date)[]) => {
    const mns = getMonthNamesShort();
    const ranges: { from: Date; to: Date }[] = [];
    const strings: string[] = [];

    [...dates]
        .sort((d1, d2) => compareAsc(new Date(d1), new Date(d2)))
        .forEach((dateStr) => {
            const date = new Date(dateStr);
            if (isValid(date)) {
                if (ranges.length === 0 || !isAdjacentDate(ranges[ranges.length - 1].to, date)) {
                    ranges.push({ from: date, to: date }); // inject new range
                } else {
                    ranges[ranges.length - 1].to = date; // extend current range
                }
            }
        });

    // loop in reverse to optimize the month names
    let prevMonth: string | null = null;
    ranges.reverse().forEach((range) => {
        if (range.from.getMonth() != range.to.getMonth()) {
            strings.push(
                `${range.from.getDate()} ${mns[range.from.getMonth()]} - ${range.to.getDate()} ${
                    mns[range.to.getMonth()]
                }`
            );
        } else {
            let label = '';
            if (isSameDay(range.to, range.from)) {
                label = range.to.getDate().toString();
            } else {
                label = `${range.from.getDate()} - ${range.to.getDate()}`;
            }
            if (!prevMonth || prevMonth != range.to.getMonth().toString()) {
                label += ' ' + mns[range.to.getMonth()];
            }
            prevMonth = range.to.getMonth().toString();
            strings.push(label);
        }
    });

    // reverse back to render in natural order.
    return strings.reverse().join(', ');
};
