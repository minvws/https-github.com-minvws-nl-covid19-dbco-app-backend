import { createPinia, setActivePinia } from 'pinia';
import { contextModalSidebarSchema } from './contextModal';
import store from '@/store';
import Calendar from '@/components/caseEditor/Calendar/Calendar.vue';
vi.mock('@/env');

describe('contextModal', () => {
    beforeEach(async () => {
        await store.dispatch('context/FILL', {
            uuid: null,
            loaded: false,
            errors: {},
            fragments: {
                general: {
                    moments: [],
                },
            },
            place: {},
        });
    });

    it('contextModalSidebarSchema should return calendar even if there are no date ranges', () => {
        setActivePinia(createPinia());

        const fields = contextModalSidebarSchema();

        expect(fields).toHaveLength(2);
        expect(fields[0].component).toBe(Calendar);
    });
});
