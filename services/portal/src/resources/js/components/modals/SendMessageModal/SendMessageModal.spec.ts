import { messageApi } from '@dbco/portal-api';
import { SharedActions } from '@/store/actions';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import { SharedMutations } from '@/store/mutations';
import { StoreType } from '@/store/storeType';
import { TaskActions } from '@/store/task/taskActions';
import type { TaskStoreState } from '@/store/task/taskStore';
import taskStore from '@/store/task/taskStore';
import { createContainer, fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { EmailLanguageV1, MessageStatusV1, MessageTemplateTypeV1, YesNoUnknownV1 } from '@dbco/enum';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import SendMessageModal from './SendMessageModal.vue';
import type { Message } from '@dbco/portal-api/message.dto';
import type { RenderedMailTemplate } from '@dbco/portal-api/mail.dto';

const message: Message = {
    uuid: fakerjs.string.uuid(),
    mailVariant: MessageTemplateTypeV1.VALUE_personalAdvice,
    caseUuid: fakerjs.string.uuid(),
    taskUuid: null,
    toEmail: fakerjs.internet.email(),
    toName: fakerjs.person.firstName(),
    telephone: fakerjs.phone.number(),
    subject: fakerjs.lorem.sentence(),
    createdAt: fakerjs.date.past().toString(),
    notificationSentAt: null,
    expiresAt: fakerjs.date.future().toString(),
    status: MessageStatusV1.VALUE_draft,
    isExpired: false,
    isDeleted: false,
    isSecure: false,
    identityRequired: false,
    isIdentified: false,
    text: fakerjs.lorem.paragraph(),
};

const template: RenderedMailTemplate = {
    subject: fakerjs.lorem.sentence(),
    body: fakerjs.lorem.paragraph(),
    footer: fakerjs.lorem.paragraph(),
    isSecure: false,
    language: EmailLanguageV1.VALUE_nl,
    attachments: [],
};

vi.spyOn(messageApi, 'getMessages').mockImplementation(() => Promise.resolve({ messages: [] }));

const spyGetEmailTemplateForCaseUuid = vi
    .spyOn(messageApi, 'getEmailTemplateForCaseUuid')
    .mockImplementation(() => Promise.resolve(template));
const spyGetEmailTemplateForContactUuid = vi
    .spyOn(messageApi, 'getEmailTemplateForContactUuid')
    .mockImplementation(() => Promise.resolve(template));

const spySendMessageToCaseSpy = vi
    .spyOn(messageApi, 'sendMessageToCase')
    .mockImplementation(() => Promise.resolve(message));
const spySendMessageToContact = vi
    .spyOn(messageApi, 'sendMessageToContact')
    .mockImplementation(() => Promise.resolve(message));

let indexState: Partial<IndexStoreState>;
let taskState: Partial<TaskStoreState>;

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        props?: object,
        data: object = {},
        indexBsnCensored: string | null = null,
        taskBsnCensored: string | null = null
    ) => {
        indexState = {
            uuid: fakerjs.string.uuid(),
            meta: { name: `${fakerjs.person.firstName()} ${fakerjs.person.lastName()}` },
            fragments: {
                index: {
                    bsnCensored: indexBsnCensored,
                },
                contact: {
                    email: fakerjs.internet.email(),
                    phone: fakerjs.phone.number(),
                },
                alternativeLanguage: {
                    useAlternativeLanguage: YesNoUnknownV1.VALUE_yes,
                    emailLanguage: EmailLanguageV1.VALUE_nl,
                },
            },
        };

        taskState = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                    email: fakerjs.internet.email(),
                    phone: fakerjs.phone.number(),
                },
                alternativeLanguage: {
                    useAlternativeLanguage: YesNoUnknownV1.VALUE_yes,
                    emailLanguage: EmailLanguageV1.VALUE_en,
                },
                personalDetails: {
                    bsnCensored: taskBsnCensored,
                },
            },
        };

        return shallowMount(SendMessageModal, {
            localVue,
            data: () => data,
            propsData: {
                caseUuid: indexState.uuid,
                mailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
                modalTitle: fakerjs.lorem.sentence(),
                modalId: fakerjs.string.uuid(),
                ...props,
            },
            store: new Vuex.Store({
                modules: {
                    index: {
                        ...indexStore,
                        actions: {
                            ...indexStore.actions,
                            [SharedMutations.UPDATE_FORM_VALUE]: vi.fn(),
                        },
                        state: {
                            ...indexStore.state,
                            ...indexState,
                        },
                    },
                    task: {
                        ...taskStore,
                        actions: {
                            ...indexStore.actions,
                            [TaskActions.UPDATE_TASK_FRAGMENT]: vi.fn(),
                        },
                        state: {
                            ...taskStore.state,
                            ...taskState,
                        },
                    },
                },
            }),
            attachTo: createContainer(),
        });
    }
);

describe('SendMessageModal.vue', () => {
    beforeEach(() => {
        vi.resetAllMocks();
    });
    it('should load template if modal is shown', async () => {
        const wrapper = createComponent();
        expect(wrapper.vm.template).toBe(null);
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('show');
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.template).not.toBe(null);
    });

    it('should clear localErrors if modal is hidden', () => {
        const data = {
            localErrors: {
                email: 'error',
            },
        };

        const wrapper = createComponent(undefined, data);
        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hide');

        expect(wrapper.vm.errors).toEqual({});
    });

    it('should show modal title if template is not loaded', () => {
        const modalTitle = fakerjs.lorem.sentence();
        const wrapper = createComponent({
            modalTitle,
        });

        expect(wrapper.find('.title').text()).toBe(modalTitle);
    });

    it('should show template title if template is loaded', () => {
        const wrapper = createComponent(undefined, { template });

        expect(wrapper.find('.title').text()).toBe(template.subject);
    });

    it('should show spinner if template not loaded', () => {
        const wrapper = createComponent();

        expect(wrapper.find('[data-testid="spinner-container"]').exists()).toBe(true);
        expect(wrapper.findComponent({ name: 'MessageTemplate' }).exists()).toBe(false);
    });

    it('should show template if template loaded', () => {
        const wrapper = createComponent(undefined, { template });

        expect(wrapper.find('[data-testid="spinner-container"]').exists()).toBe(false);
        expect(wrapper.findComponent({ name: 'MessageTemplate' }).exists()).toBe(true);
    });

    it('should show notice if the template was defaulted to NL', () => {
        const adjustedTemplate: RenderedMailTemplate = {
            ...template,
            language: EmailLanguageV1.VALUE_to,
        };

        const wrapper = createComponent(undefined, { template: adjustedTemplate });
        expect(wrapper.find('[data-testid="template-locale-notice"]').exists()).toBe(true);
    });

    it("should NOT show defaulted notice if the template isn't loaded yet", async () => {
        const wrapper = createComponent(undefined, { template });
        vi.spyOn(wrapper.vm as any, 'updateOptions').mockImplementationOnce(() =>
            setTimeout(() => Promise.resolve(template), 1000)
        );

        const optionEmail = wrapper.find('[data-testid="option-language"]');
        optionEmail.vm.$emit('change', 'to');
        await wrapper.vm.$nextTick();

        expect(wrapper.find('[data-testid="template-locale-notice"]').exists()).toBe(false);
    });

    it('should NOT show notice if the template was NOT defaulted to NL', () => {
        const wrapper = createComponent(undefined, { template });
        expect(wrapper.find('[data-testid="template-locale-notice"]').exists()).toBe(false);
    });

    it('should NOT show notice if language was NOT set (default=NL)', async () => {
        const wrapper = createComponent(undefined, { template });
        wrapper.vm.$store.state.index.fragments.alternativeLanguage.emailLanguage = null;
        await wrapper.vm.$nextTick();

        expect(wrapper.find('[data-testid="template-locale-notice"]').exists()).toBe(false);
    });

    it('should call updateOptions when ModalOption emits "change"', async () => {
        const wrapper = createComponent(undefined, { template });
        const spyUpdate = vi.spyOn(wrapper.vm as any, 'updateOptions');

        // Call forceUpdate, otherwise vi.spyOn wil miss the first call
        wrapper.vm.$forceUpdate();
        await wrapper.vm.$nextTick();

        const options = wrapper.findAllComponents({ name: 'ModalOption' });
        for (let i = 0; i < options.length; i++) {
            options.at(i).vm.$emit('change');
        }

        expect(spyUpdate).toHaveBeenCalledTimes(options.length);
    });

    it('should validate if email is filled on send', async () => {
        const wrapper = createComponent();
        const optionEmail = wrapper.find('[data-testid="option-email"]');

        optionEmail.vm.$emit('change', '     ');

        const sendMessageButton = wrapper.find('[data-testid="send-message-button"]');
        await sendMessageButton.trigger('click');

        expect(optionEmail.attributes().error).toBe('Vul een e-mailadres in');
    });

    it(`should dispatch ${StoreType.INDEX}/${SharedActions.LOAD_MESSAGES} after sending mail`, async () => {
        const wrapper = createComponent();

        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        const sendMessageButton = wrapper.find('[data-testid="send-message-button"]');
        await sendMessageButton.trigger('click');
        await flushCallStack();

        expect(spyOnDispatch).toHaveBeenCalledWith(`${StoreType.INDEX}/${SharedActions.LOAD_MESSAGES}`);
    });

    describe('Send button', () => {
        it('should be active, have text "Bericht versturen" and no spinner by default', () => {
            const wrapper = createComponent();

            const sendButton = wrapper.find('[data-testid="send-message-button"]');
            expect(sendButton.text()).toBe('Bericht versturen');
            expect(sendButton.attributes().disabled).toBeUndefined();
            expect(wrapper.find('[data-testid="send-message-spinner"]').exists()).toBe(false);
        });

        it('should be disabled, have text "Bericht versturen" and no spinner when editing an option', async () => {
            const wrapper = createComponent();
            await wrapper.findComponent({ name: 'ModalOption' }).vm.$emit('focus');

            const sendButton = wrapper.find('[data-testid="send-message-button"]');
            expect(sendButton.text()).toBe('Bericht versturen');
            expect(sendButton.attributes().disabled).toBe('true');
            expect(wrapper.find('[data-testid="send-message-spinner"]').exists()).toBe(false);
        });

        it('should be disabled, have text "Bezig met opslaan" and a spinner when saving an option', async () => {
            const wrapper = createComponent();
            await wrapper.findComponent({ name: 'ModalOption' }).vm.$emit('change', '     ');

            const sendButton = wrapper.find('[data-testid="send-message-button"]');
            expect(sendButton.text()).toBe('Bezig met opslaan');
            expect(sendButton.attributes().disabled).toBe('true');
            expect(wrapper.find('[data-testid="send-message-spinner"]').exists()).toBe(true);
        });

        it('should be disabled, have text "Bericht versturen" and a spinner when sending', async () => {
            const wrapper = createComponent();
            const sendButton = wrapper.find('[data-testid="send-message-button"]');
            await sendButton.trigger('click');

            expect(sendButton.text()).toBe('Bericht versturen');
            expect(sendButton.attributes().disabled).toBe('true');
            expect(wrapper.find('[data-testid="send-message-spinner"]').exists()).toBe(true);
        });
    });

    describe('Template NOT secure', () => {
        it('should hide ModalOption phone', () => {
            const wrapper = createComponent(undefined, { template });

            expect(wrapper.find('[data-testid="option-phone"]').exists()).toBe(false);
        });

        it('should NOT validate if phone is filled on send', async () => {
            const wrapper = createComponent();
            // Manipulate store since field is hidden
            wrapper.vm.$store.state.index.fragments.contact.phone = '     ';

            const sendMessageButton = wrapper.find('[data-testid="send-message-button"]');
            await sendMessageButton.trigger('click');

            expect(wrapper.vm.errors).toEqual({});
        });
    });

    describe('Template secure', () => {
        const adjustedTemplate: RenderedMailTemplate = {
            ...template,
            isSecure: true,
        };

        it('should show ModalOption phone', () => {
            const wrapper = createComponent(undefined, { template: adjustedTemplate });

            expect(wrapper.find('[data-testid="option-phone"]').exists()).toBe(true);
        });

        it('should validate if phone is filled on send', async () => {
            const wrapper = createComponent(undefined, { template: adjustedTemplate });
            const optionPhone = wrapper.find('[data-testid="option-phone"]');

            optionPhone.vm.$emit('change', '     ');

            const sendMessageButton = wrapper.find('[data-testid="send-message-button"]');
            await sendMessageButton.trigger('click');

            expect(optionPhone.attributes().error).toBe('Vul een telefoonnummer in');
        });
    });

    describe('index (prop "taskUuid" NOT set)', () => {
        it('should load template from case endpoint', () => {
            const wrapper = createComponent();
            wrapper.findComponent({ name: 'BModal' }).vm.$emit('show');

            expect(spyGetEmailTemplateForCaseUuid).toBeCalledTimes(1);
        });

        it('should pass expected values to options', () => {
            const secureTemplate: RenderedMailTemplate = { ...template, isSecure: true };
            const wrapper = createComponent(undefined, { template: secureTemplate });

            expect(wrapper.find('[data-testid="option-email"]').attributes().value).toBe(
                indexState.fragments!.contact!.email
            );
            expect(wrapper.find('[data-testid="option-phone"]').attributes().value).toBe(
                indexState.fragments!.contact!.phone
            );
            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBe(
                indexState.fragments!.alternativeLanguage!.emailLanguage
            );
        });

        it('should pass full name + email to ModalOption email', () => {
            const wrapper = createComponent();
            const contactString = `${indexState.meta!.name} (${indexState.fragments!.contact!.email})`;

            expect(wrapper.find('[data-testid="option-email"]').text()).toBe(contactString);
        });

        it('should pass phone number to ModalOption phone', () => {
            const secureTemplate: RenderedMailTemplate = { ...template, isSecure: true };
            const wrapper = createComponent(undefined, { template: secureTemplate });

            expect(wrapper.find('[data-testid="option-phone"]').text()).toBe(indexState.fragments!.contact!.phone);
        });

        it('should call updateIndexFragments when updateOptions is called', () => {
            const wrapper = createComponent();
            const spyUpdate = vi.spyOn(wrapper.vm as any, 'updateIndexFragments');

            wrapper.findComponent({ name: 'ModalOption' }).vm.$emit('change');

            expect(spyUpdate).toHaveBeenCalled();
        });

        it('should assume language is null if useAlternativeLanguage is not yes', async () => {
            const wrapper = createComponent();

            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBe(EmailLanguageV1.VALUE_nl);

            // Set useAlternativeLanguage to 'no'
            wrapper.vm.$store.state.index.fragments.alternativeLanguage.useAlternativeLanguage =
                YesNoUnknownV1.VALUE_no;
            await wrapper.vm.$nextTick();

            // Language option shouldn't have a value now
            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBeUndefined();
        });

        it('should set useAlternativeLanguage to yes if emailLanguage is set', async () => {
            const wrapper = createComponent();
            const spyUpdate = vi.spyOn(wrapper.vm as any, 'updateIndexFragments');

            // Set useAlternativeLanguage to 'no'
            wrapper.vm.$store.state.index.fragments.alternativeLanguage.useAlternativeLanguage =
                YesNoUnknownV1.VALUE_no;
            await wrapper.vm.$nextTick();

            // Language option shouldn't have a value now
            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBeUndefined();

            // Set language, this should set useAlternativeLanguage to 'yes'
            wrapper.find('[data-testid="option-language"]').vm.$emit('change', EmailLanguageV1.VALUE_nl);

            const expectedPost = {
                alternativeLanguage: {
                    emailLanguage: EmailLanguageV1.VALUE_nl,
                    useAlternativeLanguage: YesNoUnknownV1.VALUE_yes,
                },
                contact: indexState.fragments!.contact,
            };
            expect(spyUpdate).toHaveBeenCalledWith(expectedPost);
        });

        it('should retrieve template again if language has changed', async () => {
            const wrapper = createComponent();

            wrapper.find('[data-testid="option-language"]').vm.$emit('change', EmailLanguageV1.VALUE_de);
            await flushCallStack();

            expect(spyGetEmailTemplateForCaseUuid).toBeCalledTimes(1);
        });

        it('should NOT retrieve template again if language has not changed', async () => {
            const wrapper = createComponent();

            wrapper.find('[data-testid="option-language"]').vm.$emit('change', EmailLanguageV1.VALUE_nl);
            await flushCallStack();

            expect(spyGetEmailTemplateForCaseUuid).not.toHaveBeenCalled();
        });

        it('should call sendMessageToCase when send button is clicked', async () => {
            const customText = fakerjs.lorem.paragraph();
            const wrapper = createComponent(undefined, {
                customText,
                template,
            });

            const sendMessageButton = wrapper.find('[data-testid="send-message-button"]');
            await sendMessageButton.trigger('click');

            expect(spySendMessageToCaseSpy).toHaveBeenCalledWith(
                indexState.uuid,
                MessageTemplateTypeV1.VALUE_missedPhone,
                [],
                customText
            );
        });

        it.each([
            {
                description: 'should NOT show note next to language if BSN not known and template is insecure',
                indexBsnCensored: undefined,
                isSecure: false,
                result: false,
            },
            {
                description: 'should NOT show note next to language if BSN known and template is insecure',
                indexBsnCensored: '****123',
                isSecure: false,
                result: false,
            },
            {
                description: 'should NOT show note next to language if BSN not known and template is secure',
                indexBsnCensored: undefined,
                isSecure: true,
                result: false,
            },
            {
                description: 'should show note next to language if BSN known and template is secure',
                indexBsnCensored: '****123',
                isSecure: true,
                result: true,
            },
        ])('$description', ({ indexBsnCensored, isSecure, result }) => {
            const customTemplate: RenderedMailTemplate = {
                ...template,
                isSecure,
            };

            const wrapper = createComponent(undefined, { template: customTemplate }, indexBsnCensored);

            expect(wrapper.find('[data-testid="option-language"]').attributes().note).toBe(
                result ? 'Als ontvanger DigiD heeft kan daar ook mee worden ingelogd' : undefined
            );
        });
    });

    describe('contact (prop "taskUuid" set)', () => {
        it('should load template from task endpoint', () => {
            const props = {
                taskUuid: taskState.uuid,
            };
            const wrapper = createComponent(props);
            wrapper.findComponent({ name: 'BModal' }).vm.$emit('show');

            expect(spyGetEmailTemplateForContactUuid).toBeCalledTimes(1);
        });

        it('should pass expected values to options', () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const secureTemplate: RenderedMailTemplate = { ...template, isSecure: true };
            const wrapper = createComponent(props, { template: secureTemplate });

            expect(wrapper.find('[data-testid="option-email"]').attributes().value).toBe(
                taskState.fragments!.general!.email
            );
            expect(wrapper.find('[data-testid="option-phone"]').attributes().value).toBe(
                taskState.fragments!.general!.phone
            );
            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBe(EmailLanguageV1.VALUE_en);
        });

        it('should pass full name + email to ModalOption email', () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const wrapper = createComponent(props);

            const taskGeneralFragment = taskState.fragments!.general;
            const contactString = `${taskGeneralFragment!.firstname} ${taskGeneralFragment!.lastname} (${
                taskGeneralFragment!.email
            })`;

            expect(wrapper.find('[data-testid="option-email"]').text()).toBe(contactString);
        });

        it('should pass phone number to ModalOption phone', () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const secureTemplate: RenderedMailTemplate = { ...template, isSecure: true };
            const wrapper = createComponent(props, { template: secureTemplate });

            expect(wrapper.find('[data-testid="option-phone"]').text()).toBe(taskState.fragments!.general!.phone);
        });

        it('should call updateTaskFragments when updateOptions is called', async () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const wrapper = createComponent(props);
            const spyUpdate = vi.spyOn(wrapper.vm as any, 'updateTaskFragments');

            await wrapper.findComponent({ name: 'ModalOption' }).vm.$emit('change');

            expect(spyUpdate).toHaveBeenCalled();
        });

        it('should assume language is null if useAlternativeLanguage is not yes', async () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const wrapper = createComponent(props);

            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBe(EmailLanguageV1.VALUE_en);

            // Set useAlternativeLanguage to 'no'
            wrapper.vm.$store.state.task.fragments.alternativeLanguage.useAlternativeLanguage = YesNoUnknownV1.VALUE_no;
            await wrapper.vm.$nextTick();

            // Language option shouldn't have a value now
            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBeUndefined();
        });

        it('should set useAlternativeLanguage to yes if emailLanguage is set', async () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const wrapper = createComponent(props);
            const spyUpdate = vi.spyOn(wrapper.vm as any, 'updateTaskFragments');

            // Set useAlternativeLanguage to 'no'
            wrapper.vm.$store.state.task.fragments.alternativeLanguage.useAlternativeLanguage = YesNoUnknownV1.VALUE_no;
            await wrapper.vm.$nextTick();

            // Language option shouldn't have a value now
            expect(wrapper.find('[data-testid="option-language"]').attributes().value).toBeUndefined();

            // Set language, this should set useAlternativeLanguage to 'yes'
            wrapper.find('[data-testid="option-language"]').vm.$emit('change', EmailLanguageV1.VALUE_nl);

            const expectedPost = {
                general: taskState.fragments!.general,
                alternativeLanguage: {
                    emailLanguage: EmailLanguageV1.VALUE_nl,
                    useAlternativeLanguage: YesNoUnknownV1.VALUE_yes,
                },
            };
            expect(spyUpdate).toHaveBeenCalledWith(expectedPost);
        });

        it('should retrieve template again if language has changed', async () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const wrapper = createComponent(props);
            wrapper.find('[data-testid="option-language"]').vm.$emit('change', EmailLanguageV1.VALUE_de);
            await flushCallStack();

            expect(spyGetEmailTemplateForContactUuid).toBeCalledTimes(1);
        });

        it('should NOT retrieve template again if language has not changed', async () => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const wrapper = createComponent(props);
            wrapper.find('[data-testid="option-language"]').vm.$emit('change', EmailLanguageV1.VALUE_en);
            await flushCallStack();

            expect(spyGetEmailTemplateForContactUuid).not.toHaveBeenCalled();
        });

        it('should call sendMessageToContact wwhen send button is clicked', async () => {
            const props = {
                taskUuid: taskState.uuid,
            };
            const customText = fakerjs.lorem.paragraph();

            const wrapper = createComponent(props, {
                customText,
                template,
            });

            const sendMessageButton = wrapper.find('[data-testid="send-message-button"]');
            await sendMessageButton.trigger('click');

            expect(spySendMessageToContact).toHaveBeenCalledWith(
                indexState.uuid,
                MessageTemplateTypeV1.VALUE_missedPhone,
                props.taskUuid,
                [],
                customText
            );
        });

        it.each([
            {
                description: 'should NOT show note next to language if BSN not known and template is insecure',
                taskBsnCensored: undefined,
                isSecure: false,
                result: false,
            },
            {
                description: 'should NOT show note next to language if BSN known and template is insecure',
                taskBsnCensored: '****123',
                isSecure: false,
                result: false,
            },
            {
                description: 'should NOT show note next to language if BSN not known and template is secure',
                taskBsnCensored: undefined,
                isSecure: true,
                result: false,
            },
            {
                description: 'should show note next to language if BSN known and template is secure',
                taskBsnCensored: '****123',
                isSecure: true,
                result: true,
            },
        ])('$description', ({ taskBsnCensored, isSecure, result }) => {
            const props = {
                taskUuid: taskState.uuid,
            };

            const customTemplate: RenderedMailTemplate = {
                ...template,
                isSecure,
            };

            const wrapper = createComponent(props, { template: customTemplate }, undefined, taskBsnCensored);

            expect(wrapper.find('[data-testid="option-language"]').attributes().note).toBe(
                result ? 'Als ontvanger DigiD heeft kan daar ook mee worden ingelogd' : undefined
            );
        });
    });
});
