import type { CallQueue, QueuedMerge } from '../../sectionManagementTypes';

import getCreateQueue from './getCreateQueue';

const createQueue: CallQueue['createQueue'] = [
    {
        label: 'Test1',
        indexCount: 0,
        uuid: '456',
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

const createThenMergeQueue: QueuedMerge[] = [
    {
        target: {
            label: 'Test1',
            uuid: '456',
            indexCount: 0,
        },
        payload: ['567', '678'],
    },
];

describe('getCreateQueue', () => {
    it('should return create queue without entries that are already the target of a create and merge call.', () => {
        expect(getCreateQueue(createQueue, createThenMergeQueue)).toStrictEqual([
            {
                label: 'Test2',
            },
            {
                label: 'Test3',
            },
        ]);
    });
});
