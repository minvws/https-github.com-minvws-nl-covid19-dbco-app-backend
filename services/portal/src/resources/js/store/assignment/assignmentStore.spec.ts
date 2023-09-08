import { setActivePinia, createPinia } from 'pinia';
import { fakerjs } from '@/utils/test';
import { assignmentApi } from '@dbco/portal-api';
import { useAssignmentStore } from './assignmentStore';

describe('assignmentStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });
    afterEach(() => {
        vi.clearAllMocks();
    });

    it('should retrieve access for cases', async () => {
        const spyOnApi = vi.spyOn(assignmentApi, 'getAccessToCase').mockImplementation(() => Promise.resolve());
        const assignmentStore = useAssignmentStore();

        await assignmentStore.getAccessToCase({
            uuid: fakerjs.string.uuid(),
            token: 'test',
        });

        expect(spyOnApi).toHaveBeenCalledTimes(1);
    });
});
