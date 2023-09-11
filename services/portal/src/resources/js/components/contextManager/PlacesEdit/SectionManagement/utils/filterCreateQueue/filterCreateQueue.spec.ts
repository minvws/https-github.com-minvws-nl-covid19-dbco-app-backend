import type { CallQueue, CurrentSection } from '../../sectionManagementTypes';

import filterCreateQueue from './filterCreateQueue';

const createQueue: CallQueue['createQueue'] = [
    {
        label: 'Test1',
        indexCount: 0,
        uuid: '1',
    },
    {
        label: 'Test2',
        indexCount: 0,
        uuid: '2',
    },
    {
        label: 'Test3',
        indexCount: 0,
        uuid: '3',
    },
];

const mergeSections: CurrentSection[] = [
    {
        label: 'Test2',
        indexCount: 0,
        uuid: '2',
    },
    {
        label: 'Test4',
        indexCount: 0,
        uuid: '4',
    },
    {
        label: 'Test5',
        indexCount: 0,
        uuid: '5',
    },
];

describe('filterCreateQueue', () => {
    it('should return create queue without entries found in mergeSections.', () => {
        expect(filterCreateQueue(mergeSections, createQueue)).toStrictEqual([
            {
                label: 'Test1',
                indexCount: 0,
                uuid: '1',
            },
            {
                label: 'Test3',
                indexCount: 0,
                uuid: '3',
            },
        ]);
    });
});
