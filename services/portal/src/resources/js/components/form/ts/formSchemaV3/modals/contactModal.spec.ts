import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import type { AllowedVersions } from '..';
import {
    aboutSchema,
    contactConversationButtonToggleGroup,
    contactModalContagiousSidebarSchema,
    contactModalSchema,
    contactModalSourceSidebarSchema,
} from './contactModal';
import { createPinia, setActivePinia } from 'pinia';
import Calendar from '@/components/caseEditor/Calendar/Calendar.vue';

vi.mock('@/env');

const generator = new SchemaGenerator<AllowedVersions['task']>();

describe('contactModal', () => {
    beforeEach(async () => {
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { schemaVersion: 2 },
        });
        await store.dispatch('task/UPDATE_FRAGMENTS', {
            general: {
                category: '',
            },
            immunity: {
                isImmune: '',
            },
            inform: {
                informTarget: 'representative',
            },
        });
    });

    it('should include verzonden berichten in contactModalSchema', () => {
        expect(JSON.stringify(contactModalSchema())).toContain('Verzonden berichten');
    });
});

it(`should include component ContactConversationSendButton in contactConversationButtonToggleGroup`, () => {
    expect((contactConversationButtonToggleGroup(generator).buttonComponent as any)?.name).toBe(
        'ContactConversationSendButton'
    );
});

describe('given advice schema', () => {
    beforeEach(async () => {
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { schemaVersion: 2 },
        });
        await store.dispatch('task/UPDATE_FRAGMENTS', {
            general: {
                category: '',
            },
            immunity: {
                isImmune: '',
            },
            inform: {
                informTarget: 'representative',
            },
        });
    });

    it.todo('should not show specific isolation advice if user doesnt have dateOfLastExposure', () => {});
    it.todo('should not show specific isolation advice if user has closeContactDuringQuarantine set to true', () => {});
    it.todo(
        'should show specific isolation date advice if user has dateOfLastExposure set and closeContactDuringQuarantine is set to false',
        () => {}
    );
    it.todo(
        'should show specific isolation date advice if user has dateOfLastExposure set and closeContactDuringQuarantine is null',
        () => {}
    );

    it('aboutSchema should return extra fields when isSource is true', () => {
        const fields1 = aboutSchema(generator, false);
        const fields2 = aboutSchema(generator, true);

        expect(fields1).toHaveLength(3);
        expect(fields2).toHaveLength(5);
    });

    it('contactModalContagiousSidebarSchema should return calendar even if there are no date ranges', () => {
        setActivePinia(createPinia());

        const fields = contactModalContagiousSidebarSchema();

        expect(fields).toHaveLength(2);
        expect(fields[0].component).toBe(Calendar);
    });

    it('contactModalSourceSidebarSchema should return calendar even if there are no date ranges', () => {
        setActivePinia(createPinia());

        const fields = contactModalSourceSidebarSchema();

        expect(fields).toHaveLength(2);
        expect(fields[0].component).toBe(Calendar);
    });
});
