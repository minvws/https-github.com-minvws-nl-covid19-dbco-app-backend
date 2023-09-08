import type { CallQueue } from '../../sectionManagementTypes';

import getTargetAlreadyInQueueIndex from './getTargetAlreadyInQueueIndex';

const uuid = '123';

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

describe('getTargetAlreadyInQueueIndex', () => {
    it('should return index of section to be merged into that is already in mergeQueue as target.', () => {
        expect(getTargetAlreadyInQueueIndex(uuid, mergeQueue)).toStrictEqual(0);
    });
});
