import type { SafeHtml } from '../safeHtml';
import { generateSafeHtml, isSafeHtml } from '../safeHtml';

describe('generateSafeHtml', () => {
    const template = '<strong>{foo} {bar}</strong>';

    it('should return SafeHtml object', () => {
        const expected: SafeHtml = { html: template };

        expect(generateSafeHtml(template)).toStrictEqual(expected);
    });

    it('should replace placeholders if variables are passed', () => {
        const expected: SafeHtml = { html: '<strong>A B</strong>' };

        expect(generateSafeHtml(template, { foo: 'A', bar: 'B' })).toStrictEqual(expected);
    });

    it('should keep placeholder if variable is missing', () => {
        const expected: SafeHtml = { html: '<strong>A {bar}</strong>' };

        expect(generateSafeHtml(template, { foo: 'A' })).toStrictEqual(expected);
    });

    it('should escape HTML of passed variable', () => {
        const expected: SafeHtml = {
            html: '<strong>&lt;div&gt;&lt;/div&gt; &lt;script&gt;alert(1);&lt;/script&gt;</strong>',
        };

        expect(generateSafeHtml(template, { foo: '<div></div>', bar: '<script>alert(1);</script>' })).toStrictEqual(
            expected
        );
    });

    it('should not use regex when no variables are passed', () => {
        const replaceSpy = vi.spyOn(String.prototype, 'replace');

        generateSafeHtml(template);

        expect(replaceSpy).not.toHaveBeenCalled();
    });
});

describe('isSafeHtml', () => {
    it('should return false if passed prop is string', () => {
        expect(isSafeHtml('test')).toEqual(false);
    });

    it('should return true if passed prop is SafeHtml', () => {
        const safeHtml: SafeHtml = { html: 'test' };

        expect(isSafeHtml(safeHtml)).toEqual(true);
    });
});
