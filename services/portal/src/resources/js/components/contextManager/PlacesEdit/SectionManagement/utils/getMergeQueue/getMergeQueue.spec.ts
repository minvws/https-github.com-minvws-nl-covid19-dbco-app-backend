import type { CallQueue } from '../../sectionManagementTypes';

import getMergeQueue from './getMergeQueue';

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

describe('getMergeQueue', () => {
    it('should return merges in merge queue where target is uuid: already exists', () => {
        expect(getMergeQueue(mergeQueue)).toStrictEqual([
            {
                target: '123',
                payload: ['234', '345'],
            },
            {
                target: '789',
                payload: ['891', '198'],
            },
        ]);
    });
});
