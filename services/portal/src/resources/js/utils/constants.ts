/**
 * https://github.com/minvws/nl-covid19-dbco-app-backend-private/pull/3326
 * The reason 2020-12-01 is chosen here is because the UK started
 * putting vaccinations at this very date
 */
export const startDateCovidVaccinations = '2020-12-01';

/**
 * This constant derives from from the constant mentioned above:
 * startDateCovidVaccinations. Its exactly the day before this date.
 * It is used for validation purposes
 */
export const dayBeforeStartDateCovidVaccinations = '2020-11-30';
