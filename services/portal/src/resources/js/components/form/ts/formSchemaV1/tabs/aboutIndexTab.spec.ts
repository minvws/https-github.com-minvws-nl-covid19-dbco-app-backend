import { aboutIndexTabSchema } from './aboutIndexTab';
import store from '@/store';
import { MessageTemplateTypeV1 } from '@dbco/enum';

describe('aboutIndexTab', () => {
    beforeEach(async () => {
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { schemaVersion: 1 },
        });
    });

    it(`should include formSendEmail with template ${MessageTemplateTypeV1.VALUE_missedPhone} in the generator config`, () => {
        expect(JSON.stringify(aboutIndexTabSchema())).toContain('formSendEmail');
        expect(JSON.stringify(aboutIndexTabSchema())).toContain(MessageTemplateTypeV1.VALUE_missedPhone);
    });
});
