import type { QueuedMerge } from '../../sectionManagementTypes';

import getPreviousPayload from './getPreviousPayload';

const previousTargetsToBeMerged: QueuedMerge[] = [
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

describe('getPreviousPayload', () => {
    it('should return combined payload of previous merge targets.', () => {
        expect(getPreviousPayload(previousTargetsToBeMerged)).toStrictEqual(['567', '678', '891', '198']);
    });
});
