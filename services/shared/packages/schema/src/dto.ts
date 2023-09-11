type AnyObject = {
    [key: string]: any; // eslint-disable-line @typescript-eslint/no-explicit-any
};
/**
 * Will map the property type to a specific type used in data transfer, or the original when no mapping is required.
 * Result, maps:
 *  - Date to string
 *  - Object to a DTO object (recursive mapping)
 */
type DTOProperty<T> = T extends Date ? string : T extends AnyObject ? DTO<T> : T;

/**
 * Always use this helper to map the versioned models to their respective dataformat!
 *
 * Maps all properties from versioned interfaces to the type used in data transfer, and does so recursively for all object types found
 */
export type DTO<T extends AnyObject> = {
    readonly [P in keyof T]: DTOProperty<T[P]>;
};
