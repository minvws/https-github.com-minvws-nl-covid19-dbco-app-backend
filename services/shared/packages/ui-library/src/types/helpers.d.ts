export type StringKeys<T> = Extract<keyof T, string>;

/**
 * Create a union type of all keys of an object.
 * e.g. FlattenKeys<{ a: { b: { c: string, d:number } } }> = 'a.b.c' | 'a.b.d'
 */
export type FlattenedKeys<T> = T extends object
    ? {
          [K in keyof T]: T[K] extends infer U
              ? `${Extract<K, string>}${FlattenedKeys<U> extends '' ? '' : '.'}${FlattenedKeys<U>}`
              : never;
      }[keyof T]
    : '';

type KebabCase<T extends string, A extends string = ''> = T extends `${infer F}${infer R}`
    ? KebabCase<R, `${A}${F extends Lowercase<F> ? '' : '-'}${Lowercase<F>}`>
    : A;

/**
 * Useful for overriding a property of a type.
 *
 * @example:
 * type A = { a: string, b: number };
 * type B = ReplaceProp<A, 'a', number>; // { a: number, b: number }
 */
export type ReplaceProp<T, K extends keyof T, N> = Omit<T, K> & {
    [P in K]: N;
};

export type ReplaceFirstArg<T extends (...args: any[]) => any, TReplace> = (
    ...args: ReplaceParam<Parameters<T>, TReplace, '0'>
) => ReturnType<T>;

export type ReplaceParam<TParams extends readonly any[], TReplace, Index extends string> = {
    [K in keyof TParams]: K extends Index ? TReplace : TParams[K];
};

export type KebabKeys<T> = { [K in keyof T as K extends string ? KebabCase<K> : K]: T[K] };

export type Writeable<T> = { -readonly [P in keyof T]: T[P] };

export type MarkOptional<T, K extends keyof T> = {
    [P in K]?: T[P];
} & {
    [P in keyof Omit<T, K>]: T[P];
};

export type DeepReadonly<T> = T extends (infer R)[]
    ? DeepReadonlyArray<R>
    : T extends Function // eslint-disable-line @typescript-eslint/ban-types
    ? T
    : T extends object
    ? DeepReadonlyObject<T>
    : T;

type DeepReadonlyArray<T> = ReadonlyArray<DeepReadonly<T>>;

type DeepReadonlyObject<T> = {
    readonly [P in keyof T]: DeepReadonly<T[P]>;
};
