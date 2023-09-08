import type { CallQueue, CurrentSection } from '../../sectionManagementTypes';

import filterLabelQueue from './filterLabelQueue';

const changeLabelQueue: CallQueue['changeLabelQueue'] = [
    {
        label: 'Test1',
        uuid: '1',
    },
    {
        label: 'Test2',
        uuid: '2',
    },
    {
        label: 'Test3',
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

describe('filterLabelQueue', () => {
    it('should return change label queue without entries found in mergeSections.', () => {
        expect(filterLabelQueue(mergeSections, changeLabelQueue)).toStrictEqual([
            {
                label: 'Test1',
                uuid: '1',
            },
            {
                label: 'Test3',
                uuid: '3',
            },
        ]);
    });
});
