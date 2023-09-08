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
            meta: { schemaVersion: 7 },
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
    expect((contactConversationButtonToggleGroup(generator, []).buttonComponent as any)?.name).toBe(
        'ContactConversationSendButton'
    );
});
