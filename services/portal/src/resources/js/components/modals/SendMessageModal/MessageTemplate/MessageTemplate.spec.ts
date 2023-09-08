import { setupTest } from '@/utils/test';
import { EmailLanguageV1 } from '@dbco/enum';
import type { RenderedMailTemplate } from '@dbco/portal-api/mail.dto';
import { mount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import MessageTemplate from './MessageTemplate.vue';

const createComponent = setupTest((localVue: VueConstructor, props: object = {}, data: object = {}) =>
    mount(MessageTemplate, {
        localVue,
        propsData: props,
        data: () => data,
    })
);

describe('MessageTemplate.vue', () => {
    it('should not show custom text input if template is not secure', () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: false,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent({ template });
        expect(wrapper.find('[data-testid="custom-text"]').exists()).toBe(false);
    });

    it('should not show custom text input if template is secure, but has no placeholder', () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent({ template });
        expect(wrapper.find('[data-testid="custom-text"]').exists()).toBe(false);
    });

    it('should show custom text input if template is secure and has a placeholder', () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent({ template });
        expect(wrapper.find('[data-testid="custom-text"]').exists()).toBe(true);
    });

    it('should split body in two pieces if custom text input is shown', () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent({ template });
        const bodyDivs = wrapper.findAll('.message__body');
        expect(bodyDivs.length).toBe(2);
        expect(bodyDivs.at(0).text()).toBe('Text body');
        expect(bodyDivs.at(1).text()).toBe('after');
    });

    it('should show custom text placeholder at first', () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent({ template });
        const placeholder = wrapper.find('[data-testid="custom-text-placeholder"]');
        expect(placeholder.exists()).toBe(true);
        expect(placeholder.text()).toBe('Voeg tekst toe');
    });

    it('should show custom text input after clicking on placeholder', async () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent({ template });
        const placeholderSelector = '[data-testid="custom-text-placeholder"]';
        await wrapper.find(placeholderSelector).trigger('click');

        expect(wrapper.find(placeholderSelector).exists()).toBe(false);
        expect(wrapper.find('[data-testid="close-textarea-button"]').exists()).toBe(true);
        expect(wrapper.find('textarea').exists()).toBe(true);
    });

    it('should hide and clear custom text input after clicking on delete button', async () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent(
            { template },
            {
                customText: 'test',
                textInputVisible: true,
            }
        );

        await wrapper.find('[data-testid="close-textarea-button"]').trigger('click');

        expect(wrapper.find('[data-testid="custom-text-placeholder"]').exists()).toBe(true);
        expect(wrapper.find('textarea').exists()).toBe(false);
        expect(wrapper.vm.customText).toBeUndefined();
    });

    it('should emit "messageTemplateChange" event when changing custom text', async () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [],
        };

        const wrapper = createComponent(
            { template },
            {
                customText: 'test',
                textInputVisible: true,
            }
        );

        const textarea = wrapper.find('textarea');
        await textarea.trigger('change');
        expect(wrapper.emitted().messageTemplateChange?.[0]).toEqual([{ customText: 'test', selectedAttachments: [] }]);
    });

    it('should show checkboxes when attachments are bind to the template', () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [
                {
                    uuid: '1',
                    filename: 'Test attachment',
                },
            ],
        };

        const wrapper = createComponent({ template });

        const checkboxes = wrapper.find('[data-testid="checkbox-attachments"]');
        expect(checkboxes.exists()).toBe(true);

        const inputs = checkboxes.findAll('input');
        expect(inputs.length).toBe(1);
    });

    it('should emit "messageTemplateChange" event when selecting an attachment', async () => {
        const template: RenderedMailTemplate = {
            subject: 'Test',
            isSecure: true,
            body: 'Text body%custom_text_placeholder%after',
            footer: 'Text footer',
            language: EmailLanguageV1.VALUE_nl,
            attachments: [
                {
                    uuid: '1',
                    filename: 'Test attachment',
                },
            ],
        };

        const wrapper = createComponent(
            { template },
            {
                selectedAttachments: [1],
            }
        );
        const checkboxes = wrapper.find('[data-testid="checkbox-attachments"]');
        const inputs = checkboxes.findAll('input');

        await inputs.at(0).trigger('change');
        expect(wrapper.emitted().messageTemplateChange?.[0]).toEqual([
            { customText: undefined, selectedAttachments: [1] },
        ]);
    });
});
