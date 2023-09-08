import { createPlaceSections } from '@dbco/portal-api/client/place.api';
import type { CallQueue } from '../../sectionManagementTypes';

import makeCreateRequests from './makeCreateRequests';

vi.mock('@dbco/portal-api/client/place.api', () => ({
    createPlaceSections: vi.fn(() => Promise.resolve({ sections: [{ label: 'Test', uuid: '123', indexCount: 0 }] })),
}));

const placeUuid = '21342';

beforeEach(() => {
    vi.resetAllMocks();
});

describe('makeCreateRequests', () => {
    it('should make create requests', async () => {
        const callQueue: CallQueue = {
            changeLabelQueue: [],
            createQueue: [
                {
                    label: 'Test',
                    uuid: '123',
                    indexCount: 0,
                },
                {
                    label: 'Entree',
                    uuid: '234',
                    indexCount: 0,
                },
            ],
            mergeQueue: [],
        };
        await makeCreateRequests(placeUuid, callQueue.createQueue, callQueue.mergeQueue);
        expect(createPlaceSections).toHaveBeenCalledTimes(1);
        expect(createPlaceSections).toHaveBeenCalledWith(placeUuid, [{ label: 'Test' }, { label: 'Entree' }]);
    });
    it('should NOT make create requests for sections that are merge targets', async () => {
        const callQueue: CallQueue = {
            changeLabelQueue: [],
            createQueue: [
                {
                    label: 'Test',
                    uuid: '123',
                    indexCount: 0,
                },
                {
                    label: 'Entree',
                    uuid: '234',
                    indexCount: 0,
                },
            ],
            mergeQueue: [
                {
                    target: { label: 'Test', uuid: '123', indexCount: 0 },
                    payload: ['3425', '65544'],
                },
                {
                    target: { label: 'Entree', uuid: '234', indexCount: 0 },
                    payload: ['547674', '3453457'],
                },
            ],
        };
        await makeCreateRequests(placeUuid, callQueue.createQueue, callQueue.mergeQueue);
        expect(createPlaceSections).toHaveBeenCalledTimes(0);
    });
});
