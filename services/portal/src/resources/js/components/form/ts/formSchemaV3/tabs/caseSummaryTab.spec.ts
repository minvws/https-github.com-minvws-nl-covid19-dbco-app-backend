import { caseSummaryTabSchema } from './caseSummaryTab';
import store from '@/store';
vi.mock('@/env');

describe('caseSummaryTab', () => {
    beforeEach(async () => {
        await store.dispatch('index/FILL', {
            errors: {},
            fragments: { test: {}, symptoms: {}, medication: {} },
            meta: { schemaVersion: 1 },
        });
    });

    it('should include verzonden berichten in caseSummaryTabSchema', () => {
        expect(JSON.stringify(caseSummaryTabSchema())).toContain('Verzonden berichten');
    });
});
