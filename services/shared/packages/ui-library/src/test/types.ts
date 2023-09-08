/* eslint-disable @typescript-eslint/no-explicit-any */

import type { Mock } from 'vitest';

export type MockFunction<T extends (...args: any[]) => any> = Mock<Parameters<T>, ReturnType<T>>;

export type MockedFunctions<T extends GenericObject> = {
    [K in keyof T]: T[K] extends (...args: any[]) => any ? MockFunction<T[K]> : T[K];
};
