/* eslint-disable @typescript-eslint/no-explicit-any */

/**
 * Turns every property of the given type into optional, recursively
 */
type DeepPartial<T> = T extends object
    ? {
          [P in keyof T]?: DeepPartial<T[P]>;
      }
    : T;

type NonNullableProps<T> = { [K in keyof T]: NonNullable<T[K]> };
