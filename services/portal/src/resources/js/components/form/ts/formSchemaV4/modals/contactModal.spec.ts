import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import store from '@/store';
import type { AllowedVersions } from '..';
import { contactConversationButtonToggleGroup, contactModalSchema } from './contactModal';

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

    it(`should include component ContactConversationSendButton in contactConversationButtonToggleGroup`, () => {
        expect((contactConversationButtonToggleGroup(generator).buttonComponent as any)?.name).toBe(
            'ContactConversationSendButton'
        );
    });

    it('should include verzonden berichten in contactModalSchema', () => {
        expect(JSON.stringify(contactModalSchema())).toContain('Verzonden berichten');
    });
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
});
