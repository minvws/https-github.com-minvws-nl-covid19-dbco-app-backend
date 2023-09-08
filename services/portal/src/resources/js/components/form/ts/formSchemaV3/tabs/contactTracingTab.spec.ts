import { contactTracingTabSchema } from './contactTracingTab';

vi.mock('@/env');

describe('contactTracingTab', () => {
    it('should include extensiveContactTracing in contactTracingTabSchema', () => {
        expect(JSON.stringify(contactTracingTabSchema())).toContain('extensiveContactTracing');
    });
});
