import { createPlaceSections, mergeSections } from '@dbco/portal-api/client/place.api';
import type { CallQueue } from '../../sectionManagementTypes';

import makeCreateThenMergeRequests from './makeCreateThenMergeRequests';

vi.mock('@dbco/portal-api/client/place.api', () => ({
    mergeSections: vi.fn(() => Promise.resolve()),
    createPlaceSections: vi.fn(() => Promise.resolve({ sections: [{ label: 'Test', uuid: '123', indexCount: 0 }] })),
}));

const placeUuid = '21342';

describe('makeCreateThenMergeRequests', () => {
    it('should make chained create-then-merge requests', async () => {
        const callQueue: CallQueue = {
            changeLabelQueue: [],
            createQueue: [],
            mergeQueue: [
                {
                    target: {
                        label: 'Test',
                        uuid: '123',
                        indexCount: 0,
                    },
                    payload: ['234', '345'],
                },
            ],
        };
        await makeCreateThenMergeRequests(placeUuid, callQueue.mergeQueue);
        expect(createPlaceSections).toHaveBeenCalledTimes(1);
        expect(createPlaceSections).toHaveBeenCalledWith(placeUuid, [{ label: 'Test' }]);
        expect(mergeSections).toHaveBeenCalledTimes(1);
        expect(mergeSections).toHaveBeenCalledWith(placeUuid, '123', ['234', '345']);
    });
});
