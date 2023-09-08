import { mergeSections } from '@dbco/portal-api/client/place.api';
import type { CallQueue } from '../../sectionManagementTypes';

import makeMergeRequests from './makeMergeRequests';

vi.mock('@dbco/portal-api/client/place.api', () => ({
    mergeSections: vi.fn(() => Promise.resolve()),
}));

const placeUuid = '21342';

describe('makeMergeRequests', () => {
    it('should make merge requests', async () => {
        const callQueue: CallQueue = {
            changeLabelQueue: [],
            createQueue: [],
            mergeQueue: [
                {
                    target: '123',
                    payload: ['53265356', '56345'],
                },
                {
                    target: '234',
                    payload: ['345634', '34534'],
                },
                {
                    target: '345',
                    payload: ['76568858', '278785'],
                },
            ],
        };
        await makeMergeRequests(placeUuid, callQueue.mergeQueue);
        expect(mergeSections).toHaveBeenCalledTimes(3);
        expect(mergeSections).toHaveBeenNthCalledWith(1, placeUuid, '123', ['53265356', '56345']);
        expect(mergeSections).toHaveBeenNthCalledWith(2, placeUuid, '234', ['345634', '34534']);
        expect(mergeSections).toHaveBeenNthCalledWith(3, placeUuid, '345', ['76568858', '278785']);
    });
});
