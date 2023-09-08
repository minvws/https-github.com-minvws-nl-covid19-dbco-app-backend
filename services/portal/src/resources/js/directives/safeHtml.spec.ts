import SafeHtmlDirective from '@/directives/safeHtml';
import { generateSafeHtml } from '@/utils/safeHtml';
import type { VNodeDirective } from 'vue/types/umd';

const template = '<strong>Test</strong>';

describe('safeHtml', () => {
    it('should assign text to innerText if type is string', () => {
        const htmlEl: HTMLElement = document.createElement('div');
        const directiveValue: VNodeDirective = { name: 'safe-html', value: template };

        SafeHtmlDirective.inserted(htmlEl, directiveValue);

        expect(htmlEl.innerText).toBe(template);

        // Note: Vitest does not use a real DOM element, so if this prop is not set, it will be empty
        expect(htmlEl.innerHTML).toBeFalsy();
    });

    it('should assign text to innerHTML if type is SafeHtml', () => {
        const htmlEl: HTMLElement = document.createElement('div');
        const directiveValue: VNodeDirective = { name: 'safe-html', value: generateSafeHtml(template) };

        SafeHtmlDirective.inserted(htmlEl, directiveValue);

        expect(htmlEl.innerHTML).toBe(template);

        // Note: Vitest does not use a real DOM element, so if this prop is not set, it will be empty
        expect(htmlEl.innerText).toBeFalsy();
    });

    it('should update content if bound value changes', () => {
        const htmlEl: HTMLElement = document.createElement('div');
        const directiveValue: VNodeDirective = { name: 'safe-html', value: template };

        SafeHtmlDirective.inserted(htmlEl, directiveValue);

        expect(htmlEl.innerText).toBe(template);

        const newTemplate = '<strong>Test 2</strong>';
        directiveValue.value = newTemplate;
        SafeHtmlDirective.update(htmlEl, directiveValue);

        expect(htmlEl.innerText).toBe(newTemplate);
    });
});
