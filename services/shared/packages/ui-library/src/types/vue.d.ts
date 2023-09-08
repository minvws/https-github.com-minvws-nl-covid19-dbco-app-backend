import type { DefaultKeys, ExtractPropTypes as ExtractPropTypesVue3, OptionalKeys } from 'vue/types/v3-component-props';
import type { MarkOptional } from './helpers';

/**
 * Extracts the prop types from a Vue component props definition.
 * It fixes the issue with optional props being required in the extracted type.
 */
export type ExtractPropTypes<
    T extends GenericObject,
    P = ExtractPropTypesVue3<T>,
    K = DefaultKeys<T> | OptionalKeys<T>,
> = MarkOptional<P, Extract<keyof P, K>>;
