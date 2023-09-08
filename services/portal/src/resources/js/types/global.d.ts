/**
 * Used when the body of an object is not known
 */
interface AnyObject {
    [key: string]: any; // eslint-disable-line @typescript-eslint/no-explicit-any
}

/**
 * A date formatted as an ISO 8601 string.
 * @example: "2011-10-05T14:48:00.000Z"
 * @see: https://en.wikipedia.org/wiki/ISO_8601
 */
type DateStringISO8601 = string;
