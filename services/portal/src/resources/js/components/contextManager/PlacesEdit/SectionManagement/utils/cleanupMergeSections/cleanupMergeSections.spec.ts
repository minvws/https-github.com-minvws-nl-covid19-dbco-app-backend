import type { CallQueue, CurrentSection } from '../../sectionManagementTypes';

import cleanupMergeSections from './cleanupMergeSections';

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

describe('cleanupMergeSections', () => {
    it('should return uuids of mergeSections not found createQueue', () => {
        expect(cleanupMergeSections(mergeSections, createQueue)).toStrictEqual(['4', '5']);
    });
});
