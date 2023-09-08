import { updatePlaceSections } from '@dbco/portal-api/client/place.api';
import type { CallQueue } from '../../sectionManagementTypes';

import makeChangeLabelRequest from './makeChangeLabelRequest';

vi.mock('@dbco/portal-api/client/place.api', () => ({
    updatePlaceSections: vi.fn(() => Promise.resolve({ sections: [{ label: 'Test', uuid: '123', indexCount: 0 }] })),
}));

const placeUuid = '21342';

describe('makeChangeLabelRequest', () => {
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
        await makeChangeLabelRequest(placeUuid, callQueue.changeLabelQueue);
        expect(updatePlaceSections).toHaveBeenCalledTimes(1);
        expect(updatePlaceSections).toHaveBeenCalledWith(placeUuid, [
            { label: 'Testhal', uuid: '123' },
            { label: 'Entreehal', uuid: '234' },
        ]);
    });
});
