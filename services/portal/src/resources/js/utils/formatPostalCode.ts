/**
 * Does a small effort to make a postal code look like 1234AB. It removes spaces and transforms to uppercase.
 * @param postalCode a possibly dirty postalCode. Will return input if the input is not valid.
 */
export const formatPostalCode = (postalCode?: string) => {
    if (!postalCode) return postalCode;
    try {
        return postalCode.replaceAll(' ', '').toUpperCase();
    } catch (e) {
        return postalCode;
    }
};
