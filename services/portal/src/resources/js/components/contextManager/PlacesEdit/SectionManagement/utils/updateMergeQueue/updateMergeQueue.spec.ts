import type { CallQueue } from '../../sectionManagementTypes';

import updateMergeQueue from './updateMergeQueue';

const newPayload = ['342352', '234545'];
const previousPayload = ['876856'];

const newTarget = '996353';

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

describe('updateMergeQueue', () => {
    it('should add new merge to merge queue when the target was not already in merge queue as target.', () => {
        expect(updateMergeQueue(-1, newPayload, previousPayload, newTarget, mergeQueue)).toStrictEqual([
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
            {
                target: newTarget,
                payload: [...previousPayload, ...newPayload],
            },
        ]);
    });
    it('should just update payload of merge queue entry when the same target is merged into.', () => {
        expect(
            updateMergeQueue(
                1,
                newPayload,
                previousPayload,
                {
                    label: 'Test1',
                    uuid: '456',
                    indexCount: 0,
                },
                mergeQueue
            )
        ).toStrictEqual([
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
                payload: ['567', '678', '342352', '234545', '876856'],
            },
            {
                target: '789',
                payload: ['891', '198'],
            },
        ]);
    });
});
