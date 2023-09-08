import { updatePlaceSections, createPlaceSections, mergeSections } from '@dbco/portal-api/client/place.api';
import type { CallQueue } from '../sectionManagementTypes';

import makeAllCallQueueRequests from './makeAllCallQueueRequests';

vi.mock('@dbco/portal-api/client/place.api', () => ({
    mergeSections: vi.fn(() => Promise.resolve()),
    createPlaceSections: vi.fn(() => Promise.resolve({ sections: [{ label: 'Test', uuid: '123', indexCount: 0 }] })),
    updatePlaceSections: vi.fn(() => Promise.resolve({ sections: [{ label: 'Test', uuid: '123', indexCount: 0 }] })),
}));

const placeUuid = '21342';

describe('makeAllCallQueueRequests', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });
    it('should make change label requests', async () => {
        const callQueue: CallQueue = {
            changeLabelQueue: [
                {
                    label: 'Testhal',
                    uuid: '123',
                },
                {
                    label: 'Entreehal',
                    uuid: '234',
                },
            ],
            createQueue: [],
            mergeQueue: [],
        };
        await makeAllCallQueueRequests(placeUuid, callQueue);
        expect(updatePlaceSections).toHaveBeenCalledTimes(1);
        expect(updatePlaceSections).toHaveBeenCalledWith(placeUuid, [
            { label: 'Testhal', uuid: '123' },
            { label: 'Entreehal', uuid: '234' },
        ]);
    });
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
        await makeAllCallQueueRequests(placeUuid, callQueue);
        expect(createPlaceSections).toHaveBeenCalledTimes(1);
        expect(createPlaceSections).toHaveBeenCalledWith(placeUuid, [{ label: 'Test' }]);
        expect(mergeSections).toHaveBeenCalledTimes(1);
        expect(mergeSections).toHaveBeenCalledWith(placeUuid, '123', ['234', '345']);
    });
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
        await makeAllCallQueueRequests(placeUuid, callQueue);
        expect(createPlaceSections).toHaveBeenCalledTimes(1);
        expect(createPlaceSections).toHaveBeenCalledWith(placeUuid, [{ label: 'Test' }, { label: 'Entree' }]);
    });
    it('should make merge requests', async () => {
        const callQueue: CallQueue = {
            changeLabelQueue: [],
            createQueue: [],
            mergeQueue: [
                {
                    target: '123',
                    payload: ['234', '345'],
                },
            ],
        };
        await makeAllCallQueueRequests(placeUuid, callQueue);
        expect(mergeSections).toHaveBeenCalledTimes(1);
        expect(mergeSections).toHaveBeenCalledWith(placeUuid, '123', ['234', '345']);
    });
});
