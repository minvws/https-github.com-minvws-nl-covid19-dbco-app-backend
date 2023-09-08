import * as objectUtil from '../object';

describe('getPath', () => {
    it('should return the selected value', () => {
        const testObj = {
            level1String: 'test1',
            level1Obj: {
                level2String: 'test2',
                level2Arrs: ['aaa', 'bbb', 'ccc'],
                level2Objs: [
                    { a: 1, b: 2, c: 3 },
                    { a: 10, b: 20, c: 30 },
                    { a: 100, b: 200, c: 300 },
                ],
            },
        };

        expect(objectUtil.getPath('level1String', testObj)).toBe(testObj.level1String);

        expect(objectUtil.getPath('level1Obj.level2String', testObj)).toBe(testObj.level1Obj.level2String);
        expect(objectUtil.getPath(['level1Obj', 'level2String'], testObj)).toBe(testObj.level1Obj.level2String);

        expect(objectUtil.getPath('level1Obj.level2Arrs.1', testObj)).toBe(testObj.level1Obj.level2Arrs[1]);
        expect(objectUtil.getPath(['level1Obj', 'level2Arrs', '1'], testObj)).toBe(testObj.level1Obj.level2Arrs[1]);

        expect(objectUtil.getPath('level1Obj.level2Arrs.3', testObj)).toBe(undefined);
        expect(objectUtil.getPath('level1Obj.level2Objs.1.b', testObj)).toBe(testObj.level1Obj.level2Objs[1].b);
        expect(objectUtil.getPath('level1Obj.level2Objs.1.e', testObj)).toBe(undefined);
        expect(objectUtil.getPath('level1Obj.level2Objs.3', testObj)).toBe(undefined);
        expect(objectUtil.getPath(['level1Obj', 'level2Objs', '3'], testObj)).toBe(undefined);
    });

    it('should return the selected value with alternative selector', () => {
        const testObj = {
            level1: {
                level2: {
                    'level3.aaa.bbb.ccc': 'test',
                },
            },
        };

        expect(objectUtil.getPath('level1|level2|level3.aaa.bbb.ccc', testObj, '|')).toBe(
            testObj.level1.level2['level3.aaa.bbb.ccc']
        );
        expect(objectUtil.getPath(['level1', 'level2', 'level3.aaa.bbb.ccc'], testObj, '|')).toBe(
            testObj.level1.level2['level3.aaa.bbb.ccc']
        );

        expect(objectUtil.getPath('level1|level2|level3.aaa.bbb', testObj, '|')).toBe(undefined);
        expect(objectUtil.getPath('level1.level2', testObj, '|')).toBe(undefined);
        expect(objectUtil.getPath(['level1', 'level2'], testObj, '|')).toBe(testObj.level1.level2);
    });
});

describe('setPath', () => {
    it('should set the given path', () => {
        const testObj = {
            level1Obj: {
                level2String: 'test2',
                level2Arr: ['aaa', 'bbb', 'ccc'],
                level2Objs: [
                    { a: 1, b: 2, c: 3 },
                    { a: 10, b: 20, c: 30 },
                    { a: 100, b: 200, c: 300 },
                ],
            },
        };

        // Object: change property
        objectUtil.setPath('level1Obj.level2String', testObj, 'CHANGED');
        expect(objectUtil.getPath('level1Obj.level2String', testObj)).toBe('CHANGED');

        objectUtil.setPath('level1Obj.level2Objs.a', testObj, 123);
        expect(objectUtil.getPath('level1Obj.level2Objs.a', testObj)).toBe(123);

        // Object: add new property
        objectUtil.setPath('level1Obj.newString', testObj, 'NEWVALUE');
        expect(objectUtil.getPath('level1Obj.newString', testObj)).toBe('NEWVALUE');

        // Array: change item
        objectUtil.setPath('level1Obj.level2Arr.1', testObj, 'zzz');
        expect(objectUtil.getPath('level1Obj.level2Arr.1', testObj)).toBe('zzz');

        // Array: add item
        objectUtil.setPath('level1Obj.level2Arr.3', testObj, 'abc');
        expect(objectUtil.getPath('level1Obj.level2Arr.3', testObj)).toBe('abc');
        objectUtil.setPath('level1Obj.level2Arr.99', testObj, 'xyz');
        expect(objectUtil.getPath('level1Obj.level2Arr.99', testObj)).toBe('xyz');

        // Try to assign new property on non-existing property
        objectUtil.setPath('level1Obj.level2Objs.eee.newStringOnNonExistingObj', testObj, 123);
        expect(objectUtil.getPath('level1Obj.level2Objs.eee.newStringOnNonExistingObj', testObj)).toBe(undefined);

        // Assign root
        objectUtil.setPath('level1Obj', testObj, { a: 1 });
        expect(objectUtil.getPath('level1Obj', testObj)).toEqual({ a: 1 });
    });
});

describe('sortByValue', () => {
    it('should sort the given object by value', () => {
        const testObj = {
            a: 'z',
            b: 'y',
            c: 'x',
        };

        const sortedObj = objectUtil.sortByValue(testObj);
        expect(sortedObj).toEqual({
            c: 'x',
            b: 'y',
            a: 'z',
        });
    });
});

describe('flatten/unflatten', () => {
    it('should flatten/unflatten only the first level of an object', () => {
        const input = {
            general: {
                firstName: 'John',
                subObject: {
                    a: 1,
                },
            },
        };

        const expectedOutput = {
            'general.firstName': 'John',
            'general.subObject': { a: 1 },
        };

        expect(objectUtil.flatten(input)).toEqual(expectedOutput);
        expect(objectUtil.unflatten(expectedOutput)).toEqual(input);
    });

    it('should flatten/unflatten an object as expected', () => {
        const input = {
            general: {
                item: 'text',
                boolean: true,
                objectArray: [
                    {
                        a: 1,
                        b: 2,
                    },
                    {
                        c: 3,
                        d: 4,
                    },
                ],
                subObject: {
                    a: 1,
                    b: 2,
                },
                array: ['a', 'b', 'c'],
            },
        };

        const expectedOutput = {
            'general.item': 'text',
            'general.boolean': true,
            'general.objectArray': [
                {
                    a: 1,
                    b: 2,
                },
                {
                    c: 3,
                    d: 4,
                },
            ],
            'general.subObject': {
                a: 1,
                b: 2,
            },
            'general.array': ['a', 'b', 'c'],
        };

        expect(objectUtil.flatten(input)).toEqual(expectedOutput);
        expect(objectUtil.unflatten(expectedOutput)).toEqual(input);
    });

    it('should flatten/unflatten an array as expected', () => {
        const input = {
            general: ['a', 'b', 'c'],
            work: [
                {
                    a: 1,
                },
                {
                    b: 2,
                },
                {
                    c: 3,
                },
            ],
            medical: [true, false, null],
            nonArray: {
                0: 'a',
                1: 'b',
                test: 'c',
            },
        };

        const expectedOutput = {
            'general.0': 'a',
            'general.1': 'b',
            'general.2': 'c',
            'work.0': { a: 1 },
            'work.1': { b: 2 },
            'work.2': { c: 3 },
            'medical.0': true,
            'medical.1': false,
            'medical.2': null,
            'nonArray.0': 'a',
            'nonArray.1': 'b',
            'nonArray.test': 'c',
        };

        expect(objectUtil.flatten(input)).toEqual(expectedOutput);
        expect(objectUtil.unflatten(expectedOutput)).toEqual(input);
    });

    it('should not flatten/unflatten non-objects/arrays', () => {
        const inputAndOutput = {
            boolean: true,
            empty: null,
            number: 123,
            number2: 123.456,
            text: 'string',
            symbol: Symbol('foo'),
        };

        expect(objectUtil.flatten(inputAndOutput)).toEqual(inputAndOutput);
        expect(objectUtil.unflatten(inputAndOutput)).toEqual(inputAndOutput);
    });
});

describe('removeNullable', () => {
    it('should remove null and undefined values', () => {
        const symbol = Symbol('foo');

        const input = {
            null: null,
            undefined: undefined,
            boolean: true,
            empty: null,
            number: 123,
            number2: 123.456,
            text: 'string',
            symbol,
        };

        const expectedOutput = {
            boolean: true,
            number: 123,
            number2: 123.456,
            text: 'string',
            symbol,
        };

        expect(objectUtil.removeNullValues(input)).toEqual(expectedOutput);
    });
});
