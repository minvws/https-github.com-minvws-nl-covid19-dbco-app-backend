/**
 * Gets a property from an object based on a given path
 * @param path string or array of strings indicating the path to the desired property
 * @param obj object from which the desired property should be retrieved
 * @param separator character(s) on which param path will be split if it is a string
 * @returns the desired property of the object
 */
export const getPath = (path: string | string[], obj: any, separator = '.'): any => {
    const properties = Array.isArray(path) ? path : path.split(separator);
    return properties.reduce((prev, curr) => prev && prev[curr], obj);
};

/**
 * Sets a property on an object based on a given path
 * @param path string or array of strings indicating the path to the desired property
 * @param obj object from which the desired property should be retrieved
 * @param value value which will be set to the desired property
 * @param separator character(s) on which param path will be split if it is a string
 */
export const setPath = (path: string | string[], obj: any, value: any, separator = '.'): void => {
    const properties = Array.isArray(path) ? path : path.split(separator);
    const lastProperty = properties.pop();

    // Get parent of variable to get a reference
    const resolvedParent = getPath(properties, obj, separator);

    // If parent is found and there is a last property, set it
    if (resolvedParent && lastProperty) {
        resolvedParent[lastProperty] = value;
    }
};

/**
 * Sorts an object by its value
 * @param obj object which should be sorted by value
 * @returns sorted object
 */
export const sortByValue = (obj: Record<string, string>): Record<string, string> =>
    Object.fromEntries(Object.entries(obj).sort(([, a], [, b]) => a.localeCompare(b)));

// Types for flatten/unflatten
type InferParent<T> = T extends `${infer Parent}.${string}` ? Parent : never;
type InferChild<T> = T extends `${string}.${infer Child}` ? Child : never;

type Unflatten<T> = {
    // Split the key by dot notation and create a nested object
    [Property in keyof T as InferParent<Property>]: {
        [ChildProperty in InferChild<Property>]: T[`${InferParent<Property>}.${ChildProperty}` & keyof T];
    };
} & {
    // Keep the key as is if it is not a dot notation
    [Property in keyof T as Exclude<Property, `${string}.${string}`>]: T[Property];
};

/**
 * Flattens an object 1 level deep to send use it in our forms
 *
 * @example
 * {
 *  general: {
 *    firstName: 'Poca',
 *    lastName: 'Hontas'
 *  },
 *  personalDetails: {
 *    address: {
 *      postalCode: '1234AB'
 *    }
 *  }
 * }
 * to:
 * {
 *  general.firstName: 'Poca',
 *  general.lastName: 'Hontas',
 *  personalDetails.address: {
 *    postalCode: '1234AB'
 *  }
 * }
 * @param obj Object to flatten
 * @returns Flattened object
 */
export const flatten = (obj: { [key: string]: any }) => {
    return Object.keys(obj).reduce(
        (acc, curr) => {
            // Do not flatten if null or not an array/object
            if (!obj[curr] || (!Array.isArray(obj[curr]) && typeof obj[curr] !== 'object')) {
                acc[curr] = obj[curr];
                return acc;
            }

            Object.entries(obj[curr]).forEach(([key, value]) => {
                acc[`${curr}.${key}`] = value;
            });
            return acc;
        },
        {} as { [key: string]: any }
    );
};

/**
 * Creates a nested object form a flattened object.
 * @example
 * {
 *  'general.firstName': 'Poca',
 *  'general.lastName': 'Hontas',
 *  'personalDetails.address': {
 *    postalCode: '1234AB'
 *  }
 * }
 * to:
 * {
 *  general: {
 *    firstName: 'Poca',
 *    lastName: 'Hontas'
 *  },
 *  personalDetails: {
 *    address: {
 *      postalCode: '1234AB'
 *    }
 *  }
 * }
 * @param obj Form object to unflatten
 * @returns Nested object
 */

export const unflatten = <T extends AnyObject>(obj: T): Unflatten<T> => {
    const splitKeys = Object.keys(obj).map((key) => key.split('.'));

    return Object.entries(obj).reduce((acc, [key, value]) => {
        if (!key.includes('.')) {
            acc[key] = value;
            return acc;
        }

        const [fragmentKey, ...fieldKey] = key.split('.');
        if (fieldKey) {
            // Checks if there is a flattened array
            // TRUE: general.0, general.1, general.2
            // FALSE: general.0, general.1, general.test
            const isArray = splitKeys
                .filter((key) => key[0] === fragmentKey)
                .every((key) => !isNaN(parseFloat(key[1])));

            acc[fragmentKey] = acc[fragmentKey] || (isArray ? [] : {});
            acc[fragmentKey][fieldKey.join('.')] = value;
        } else {
            acc[fragmentKey] = value;
        }

        return acc;
    }, {} as AnyObject) as Unflatten<T>;
};

export const removeNullValues = <T extends AnyObject>(obj: T): NonNullableProps<T> => {
    return Object.entries(obj).reduce(
        (acc, [key, value]) => {
            if (value !== null && value !== undefined) {
                acc[key as keyof T] = value;
            }
            return acc;
        },
        {} as Exclude<T, null>
    );
};
