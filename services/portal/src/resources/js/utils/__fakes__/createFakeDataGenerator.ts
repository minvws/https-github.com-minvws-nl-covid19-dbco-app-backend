import type { DeepReadonly } from 'deep-freeze';
import deepFreeze from 'deep-freeze';
import { defaultsDeep } from 'lodash';

type TestData<T> = AnyObject | Array<T>;

export type FakeDataGenerator<T extends TestData<T>> = {
    (partialData?: DeepPartial<T>, frozen?: true): DeepReadonly<T>;
    (partialData?: DeepPartial<T>, frozen?: false): T;
    (partialData?: DeepPartial<T>, frozen?: boolean): T | DeepReadonly<T>;
};

export type FakeDataCollectionGenerator<T extends TestData<T>> = {
    (times: number, partialData?: DeepPartial<T>, frozen?: true): Readonly<DeepReadonly<T>[]>;
    (times: number, partialData?: DeepPartial<T>, frozen?: false): T[];
    (times: number, partialData?: DeepPartial<T>, frozen?: boolean): T[] | Readonly<DeepReadonly<T>[]>;
};

/**
 * Returns a function that can be used for creating test data.
 */
export function createFakeDataGenerator<T extends TestData<T>>(defaultData: () => T): FakeDataGenerator<T> {
    const isArray = Array.isArray(defaultData());

    return (partialData, frozen = true) => {
        const testData = defaultsDeep(isArray ? [] : {}, partialData, defaultData());
        return frozen ? deepFreeze(testData) : testData;
    };
}

/**
 * Returns a function that can be used for creating a test data collections.
 */
export function createFakeDataCollectionGenerator<T extends TestData<T>>(fakeDataGenerator: FakeDataGenerator<T>) {
    const collectionGenerator = (times: number, partialData: DeepPartial<T>, frozen = true) => {
        const testDataCollection = [];
        for (let i = 0; i < times; i++) {
            testDataCollection.push(fakeDataGenerator(partialData, frozen));
        }
        return frozen ? Object.freeze(testDataCollection) : testDataCollection;
    };

    return collectionGenerator as FakeDataCollectionGenerator<T>;
}
