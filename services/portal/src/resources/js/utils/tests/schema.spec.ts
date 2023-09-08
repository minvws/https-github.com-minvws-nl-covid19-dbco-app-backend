import { unflatten } from '../object';
import * as schemaUtil from '../schema';

describe('processRules', () => {
    // Hide console logs
    vi.spyOn(console, 'log').mockImplementation(() => {});

    it('should update values if changed by a rule', () => {
        const rules = [
            {
                title: 'Test',
                watch: 'general.test',
                callback: () => ({
                    'general.otherField': 'change',
                }),
            },
        ];

        const oldValues = {
            'general.test': 'oldvalue',
            'general.otherField': 'test',
        };

        const newValues = {
            'general.test': 'newvalue',
            'general.otherField': 'test',
        };

        const result = schemaUtil.processRules(rules, oldValues, newValues);

        expect(result).toEqual({
            'general.test': 'newvalue',
            'general.otherField': 'change',
        });
    });

    it('should not update values if nothing is changing', () => {
        const rules = [
            {
                title: 'Test',
                watch: 'general.test',
                callback: vi.fn(() => ({})),
            },
        ];

        const oldValues = {
            'general.somethingElse': 'oldvalue',
            'general.otherField': 'test',
        };

        const newValues = {
            'general.somethingElse': 'newvalue',
            'general.otherField': 'test',
        };

        const result = schemaUtil.processRules(rules, oldValues, newValues);

        expect(result).toEqual(newValues);
        expect(rules[0].callback).not.toHaveBeenCalled();
    });

    it('should not do anything if values are the same', () => {
        const rules = [
            {
                title: 'Test',
                watch: 'general.test',
                callback: vi.fn(() => ({})),
            },
        ];

        const values = {
            'general.test': 'oldvalue',
            'general.otherField': 'test',
        };

        const result = schemaUtil.processRules(rules, values, values);

        expect(result).toEqual(values);
        expect(rules[0].callback).not.toHaveBeenCalled();
    });

    it('should be able to watch multiple fields', () => {
        const rules = [
            {
                title: 'Test',
                watch: ['work.fieldA', 'work.fieldB'],
                callback: () => ({
                    'work.fieldC': 'change',
                }),
            },
        ];

        const oldValues = {
            'work.fieldA': '1',
            'work.fieldB': '2',
        };

        const newValues = {
            'work.fieldA': '1',
            'work.fieldB': 'changedValue',
        };

        const result = schemaUtil.processRules(rules, oldValues, newValues);

        expect(result).toEqual({
            'work.fieldA': '1',
            'work.fieldB': 'changedValue',
            'work.fieldC': 'change',
        });
    });

    it('should pass the right params to the callback', () => {
        const rules = [
            {
                title: 'Test',
                watch: ['work.fieldA', 'work.fieldB'],
                callback: vi.fn(() => ({})),
            },
        ];

        const oldValues = {
            'work.fieldA': '1',
            'work.fieldB': '2',
        };

        const newValues = {
            'work.fieldA': '1',
            'work.fieldB': 'changedValue',
        };

        schemaUtil.processRules(rules, oldValues, newValues);

        const expectedData = unflatten(newValues);
        expect(rules[0].callback).toHaveBeenCalledWith(expectedData, ['1', 'changedValue'], ['1', '2']);
    });
});

describe('getInputNames', () => {
    it('should return input names from a schema arrays', () => {
        const schema = [
            {
                name: 'fragment.field1',
            },
            {
                name: 'fragment2.field2',
            },
        ];

        const names = schemaUtil.getInputNames(schema);

        expect(names).toEqual(['fragment.field1', 'fragment2.field2']);
    });

    it('should return input names from children schema arrays', () => {
        const schema = [
            {
                name: 'fragment.field1',
            },
            {
                name: 'fragment2.field2',
            },
            {
                children: [
                    {
                        name: 'fragment3.field3',
                    },
                ],
            },
        ];

        const names = schemaUtil.getInputNames(schema);

        expect(names).toEqual(['fragment.field1', 'fragment2.field2', 'fragment3.field3']);
    });

    it('should return input names from children schema arrays and the name of the item with children, when applicable', () => {
        const schema = [
            {
                name: 'fragment.field1',
            },
            {
                name: 'fragment2.field2',
            },
            {
                name: 'fragment2.fieldwithchildren',
                children: [
                    {
                        name: 'fragment3.field3',
                    },
                ],
            },
        ];

        const names = schemaUtil.getInputNames(schema);

        expect(names).toEqual([
            'fragment.field1',
            'fragment2.field2',
            'fragment2.fieldwithchildren',
            'fragment3.field3',
        ]);
    });
});
