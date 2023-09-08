import { truncate } from './truncate/truncate';
import { age } from './age/age';
import { categoryFormat, placeCategoryImageClass } from './category/category';
import {
    dateFnsFormat,
    dateFormatDeltaTime,
    dateFormatMonth,
    dateFormat,
    dateTimeFormatLong,
    dateFormatLong,
    dateLast3Days,
    dateFormatMonthLong,
} from './date/date';

export function useFilters() {
    return {
        truncate,
        age,
        categoryFormat,
        placeCategoryImageClass,
        dateFnsFormat,
        dateFormatDeltaTime,
        dateFormatMonth,
        dateFormat,
        dateFormatLong,
        dateTimeFormatLong,
        dateLast3Days,
        dateFormatMonthLong,
    };
}
