import type { CallQueue } from '../../sectionManagementTypes';

import getCreateThenMergeQueue from './getCreateThenMergeQueue';

const mergeQueue: CallQueue['mergeQueue'] = [
    {
        target: '123',
        payload: ['234', '345'],
    },
    {
        target: {
            label: 'Test1',
            uuid: '456',
            indexCount: 0,
        },
        payload: ['567', '678'],
    },
    {
        target: '789',
        payload: ['891', '198'],
    },
];

describe('getCreateThenMergeQueue', () => {
    it('should return merge queue without entries that only have a uuid', () => {
        expect(getCreateThenMergeQueue(mergeQueue)).toStrictEqual([
            {
                target: {
                    label: 'Test1',
                    uuid: '456',
                    indexCount: 0,
                },
                payload: ['567', '678'],
            },
        ]);
    });
});
