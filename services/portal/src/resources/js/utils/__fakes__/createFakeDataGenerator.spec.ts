import { fakerjs } from '@/utils/test';
import { uniq } from 'lodash';
import { v4 as uuidv4 } from 'uuid';
import { createFakeDataGenerator, createFakeDataCollectionGenerator } from './createFakeDataGenerator';

const { number, lorem } = fakerjs;

type TestData = {
    foo: string;
    bar: number;
};

type DeepTestData = {
    id: string;
    data: TestData;
};

describe('createFakeDataGenerator', () => {
    it('should return a fake data generator', () => {
        const defaultData: TestData = {
            foo: lorem.word(),
            bar: number.int(),
        };

        const fakeData = createFakeDataGenerator<TestData>(() => defaultData);
        const fakeDataResult = fakeData();

        expect(fakeDataResult).toEqual(defaultData);
        expect(fakeDataResult).not.toBe(defaultData);
    });

    it('generator should allow properties to be overridden', () => {
        const defaultData: TestData = {
            foo: lorem.word(),
            bar: number.int(),
        };

        const fakeData = createFakeDataGenerator<TestData>(() => defaultData);
        const fakeDataResult = fakeData({
            foo: 'some other value',
        });

        expect(fakeDataResult.foo).toEqual('some other value');
        expect(fakeDataResult.bar).toEqual(defaultData.bar);
    });

    it('fake data should throw if (existing) data is modified afterwards', () => {
        const defaultData: TestData = {
            foo: lorem.word(),
            bar: number.int(),
        };

        const fakeData = createFakeDataGenerator<TestData>(() => defaultData);
        const fakeDataResult = fakeData();

        const modifyData = () => {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (fakeDataResult as any).foo = lorem.word();
        };

        expect(() => modifyData()).toThrowError();
    });

    it('fake data should not throw if data is modified afterwards when not frozen', () => {
        const defaultData: TestData = {
            foo: lorem.word(),
            bar: number.int(),
        };

        const fakeData = createFakeDataGenerator<TestData>(() => defaultData);
        const fakeDataResult = fakeData(undefined, false);
        const newFoo = lorem.word();

        const modifyData = () => {
            fakeDataResult.foo = newFoo;
        };

        expect(() => modifyData()).not.toThrowError();
        expect(fakeDataResult.foo).toBe(newFoo);
    });

    it('fake data generator should allow modification of deep data sets', () => {
        const defaultDeepData: DeepTestData = {
            id: uuidv4(),
            data: {
                foo: lorem.word(),
                bar: number.int(),
            },
        };

        const fakeData = createFakeDataGenerator<DeepTestData>(() => defaultDeepData);
        const fakeDataResult = fakeData({
            data: {
                foo: 'some other value',
            },
        });

        expect(fakeDataResult).toEqual({
            id: defaultDeepData.id,
            data: {
                foo: 'some other value',
                bar: defaultDeepData.data.bar,
            },
        });
    });

    it('fake data generator should handle Arrays', () => {
        const defaultData: TestData = {
            foo: lorem.word(),
            bar: number.int(),
        };
        const fakeData = createFakeDataGenerator<TestData[]>(() => [defaultData, defaultData, defaultData]);
        const [one, two, three, four] = fakeData([{ foo: 'some other value' }]);

        expect(one).toEqual({
            foo: 'some other value',
            bar: defaultData.bar,
        });

        expect(two).toEqual(defaultData);
        expect(two).not.toBe(defaultData);

        expect(three).toEqual(defaultData);
        expect(three).not.toBe(defaultData);

        expect(four).toBeUndefined();
    });
});

describe('createFakeDataCollectionGenerator', () => {
    const fakeData = createFakeDataGenerator<DeepTestData>(() => ({
        id: uuidv4(),
        data: {
            foo: lorem.word(),
            bar: number.int(),
        },
    }));
    const fakeDataCollection = createFakeDataCollectionGenerator(fakeData);

    it('fake data collection should return an array with x items', () => {
        const collectionLength = number.int({ max: 20, min: 2 });
        const fakeDataCollectionResult = fakeDataCollection(collectionLength);
        const uniqueIds = uniq(fakeDataCollectionResult.map(({ id }) => id));

        expect(fakeDataCollectionResult.length).toEqual(collectionLength);
        expect(uniqueIds.length).toEqual(collectionLength);
    });

    it('should allow to override collection data', () => {
        const collectionLength = number.int({ max: 20, min: 2 });
        const fakeDataCollectionResult = fakeDataCollection(collectionLength, { id: 'not-a-uuid' });
        const uniqueIds = uniq(fakeDataCollectionResult.map(({ id }) => id));

        expect(fakeDataCollectionResult.length).toEqual(collectionLength);
        expect(uniqueIds.length).toEqual(1);
        expect(uniqueIds[0]).toEqual('not-a-uuid');
    });

    it('fake data collection should throw if (existing) data is modified afterwards', () => {
        const collectionLength = number.int({ max: 20, min: 2 });
        const fakeDataCollectionResult = fakeDataCollection(collectionLength);

        const modifyData = () => {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (fakeDataCollectionResult as any).push({
                id: uuidv4(),
                data: {
                    foo: lorem.word(),
                    bar: number.int(),
                },
            });
        };

        expect(() => modifyData()).toThrowError();
    });

    it('fake data collection should not throw if data is modified afterwards and unfrozen', () => {
        const collectionLength = number.int({ max: 20, min: 2 });
        const fakeDataCollectionResult = fakeDataCollection(collectionLength, undefined, false);

        const modifyData = () => {
            fakeDataCollectionResult.push({
                id: uuidv4(),
                data: {
                    foo: lorem.word(),
                    bar: number.int(),
                },
            });
        };

        expect(() => modifyData()).not.toThrowError();
        expect(fakeDataCollectionResult.length).toBe(collectionLength + 1);
    });
});
