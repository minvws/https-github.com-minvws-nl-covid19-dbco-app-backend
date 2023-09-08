import { aboutIndexTabSchema } from './aboutIndexTab';

vi.mock('@/env');

describe('aboutIndexTab', () => {
    it('should include extensiveContactTracing in contactTracingTabSchema', () => {
        expect(JSON.stringify(aboutIndexTabSchema())).toContain('extensiveContactTracing');
    });
});
