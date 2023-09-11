import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import type { AllowedVersions } from '..';
import {
    aboutSchema,
    closeContactDuringQuarantineFields,
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
            meta: { schemaVersion: 1 },
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

    it(`should include component ContactConversationSendButton in contactConversationButtonToggleGroup`, () => {
        expect((contactConversationButtonToggleGroup(generator).buttonComponent as any)?.name).toBe(
            'ContactConversationSendButton'
        );
    });

    it('should include verzonden berichten in contactModalSchema', () => {
        expect(JSON.stringify(contactModalSchema())).toContain('Verzonden berichten');
    });

    it('closeContactDuringQuarantineSchema should return the general.closeContactDuringQuarantine field if isSource is false', () => {
        const fields = closeContactDuringQuarantineFields(generator, false);

        expect(fields).toHaveLength(2);
    });

    it('closeContactDuringQuarantineSchema should not return the general.closeContactDuringQuarantine field if isSource is true', () => {
        const fields = closeContactDuringQuarantineFields(generator, true);

        expect(fields).toHaveLength(1);
    });

    it('aboutSchema should return extra fields when isSource is true', () => {
        const fields1 = aboutSchema(generator, false);
        const fields2 = aboutSchema(generator, true);

        expect(fields1).toHaveLength(3);
        expect(fields2).toHaveLength(5);
    });

    it('contactModalContagiousSidebarSchema should return calendar even if there are no date ranges', () => {
        setActivePinia(createPinia());

        const fields = contactModalContagiousSidebarSchema();

        expect(fields).toHaveLength(3);
        expect(fields[1].component).toBe(Calendar);
    });

    it('contactModalSourceSidebarSchema should return calendar even if there are no date ranges', () => {
        setActivePinia(createPinia());

        const fields = contactModalSourceSidebarSchema();

        expect(fields).toHaveLength(2);
        expect(fields[0].component).toBe(Calendar);
    });
});
