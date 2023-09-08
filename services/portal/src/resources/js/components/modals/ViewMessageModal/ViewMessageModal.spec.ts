import { messageApi } from '@dbco/portal-api';
import { fakerjs, setupTest } from '@/utils/test';
import { MessageStatusV1, MessageTemplateTypeV1 } from '@dbco/enum';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import SendMessageModal from './ViewMessageModal.vue';
import type { Message } from '@dbco/portal-api/message.dto';

const message: Message = {
    uuid: fakerjs.string.uuid(),
    mailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
    caseUuid: fakerjs.string.uuid(),
    taskUuid: null,
    toEmail: fakerjs.internet.email(),
    toName: fakerjs.person.firstName(),
    telephone: fakerjs.phone.number(),
    subject: fakerjs.lorem.sentence(),
    text: fakerjs.lorem.paragraph(),
    createdAt: fakerjs.date.past().toString(),
    notificationSentAt: fakerjs.date.past().toString(),
    expiresAt: fakerjs.date.soon().toString(),
    status: MessageStatusV1.VALUE_draft,
    isExpired: false,
    isDeleted: false,
    isSecure: true,
    identityRequired: false,
    isIdentified: true,
    hasAttachments: false,
    attachments: [],
};

const modalTitle = fakerjs.lorem.sentence();

const spyGetMessage = vi.spyOn(messageApi, 'getMessage').mockImplementation(() => Promise.resolve(message));
const datetimeFormatLongMock = vi.fn();

const createComponent = setupTest(async (localVue: VueConstructor, initOpened = false) => {
    const props = {
        caseUuid: message.caseUuid,
        messageUuid: message.uuid,
        modalTitle: modalTitle,
        modalId: fakerjs.string.alpha(10),
    };

    const wrapper = shallowMount(SendMessageModal, {
        localVue,
        propsData: props,
        mocks: {
            $filters: {
                dateTimeFormatLong: datetimeFormatLongMock,
            },
        },
    });

    if (initOpened) {
        wrapper.findComponent({ name: 'BModal' }).vm.$emit('show');
        await wrapper.vm.$nextTick();
    }

    return wrapper;
});

describe('SendMessageModal.vue', () => {
    it('should not load message if not shown', async () => {
        const wrapper = await createComponent();

        expect(wrapper.vm.message).toBe(null);
    });

    it('should load message if modal is shown', async () => {
        const wrapper = await createComponent(true);

        expect(wrapper.vm.message).not.toBe(null);
    });

    it('should show modal title and hide message info if message is not loaded', async () => {
        const wrapper = await createComponent();

        expect(wrapper.find('.title').text()).toBe(modalTitle);
        expect(wrapper.find('[data-testid="message-info"]').exists()).toBe(false);
    });

    it('should show message title and message info if message is loaded', async () => {
        const wrapper = await createComponent(true);

        expect(wrapper.find('.title').text()).toBe(message.subject);
        expect(wrapper.find('[data-testid="message-info"]').exists()).toBe(true);
    });

    it('should show spinner if message not loaded', async () => {
        const wrapper = await createComponent();

        expect(wrapper.find('[data-testid="spinner-container"]').exists()).toBe(true);
        expect(wrapper.find('.message').exists()).toBe(false);
    });

    it('should show message if message loaded', async () => {
        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="spinner-container"]').exists()).toBe(false);
        expect(wrapper.find('.message').exists()).toBe(true);
    });

    it('should show expiration datetime if set', async () => {
        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="expires-at-label"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="expires-at-value"]').exists()).toBe(true);
    });

    it('should NOT show expiration datetime if not set', async () => {
        const differentMessage: Message = {
            ...message,
            expiresAt: null,
        };
        spyGetMessage.mockReturnValueOnce(Promise.resolve(differentMessage));

        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="expires-at-label"]').exists()).toBe(false);
        expect(wrapper.find('[data-testid="expires-at-value"]').exists()).toBe(false);
    });

    it('should emit "hide" if modal is hidden', async () => {
        const wrapper = await createComponent(true);
        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hide');
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('hide')).toEqual([[]]);
    });

    it('should show login method if isSecure=true', async () => {
        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="login-method-label"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="login-method-value"]').exists()).toBe(true);
    });

    it('should NOT show login method if isSecure=false', async () => {
        const differentMessage: Message = {
            ...message,
            isSecure: false,
        };
        spyGetMessage.mockReturnValueOnce(Promise.resolve(differentMessage));

        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="login-method-label"]').exists()).toBe(false);
        expect(wrapper.find('[data-testid="login-method-value"]').exists()).toBe(false);
    });

    it('should show SMS login method if telephone!=null and isIdentified=false', async () => {
        const differentMessage: Message = {
            ...message,
            isIdentified: false,
        };
        spyGetMessage.mockReturnValueOnce(Promise.resolve(differentMessage));

        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="login-method-value"]').text()).toBe(`SMS-code via ${message.telephone}`);
    });

    it('should show DigiD login method if telephone=null and isIdentified=true', async () => {
        const differentMessage: Message = {
            ...message,
            telephone: null,
        };
        spyGetMessage.mockReturnValueOnce(Promise.resolve(differentMessage));

        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="login-method-value"]').text()).toBe('Inloggen met DigiD');
    });

    it('should show both login methods if telephone!=null and isIdentified=true', async () => {
        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="login-method-value"]').text()).toBe(
            `SMS-code via ${message.telephone} of inloggen met DigiD`
        );
    });

    it('should show attachments if attachments not empty', async () => {
        const differentMessage: Message = {
            ...message,
            attachments: [
                {
                    uuid: 'test',
                    fileName: 'test',
                    createdAt: 'test',
                    updatedAt: 'test',
                    inactiveSince: 'test',
                },
            ],
        };
        spyGetMessage.mockReturnValueOnce(Promise.resolve(differentMessage));

        const wrapper = await createComponent(true);

        expect(wrapper.find('[data-testid="attachment-test"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="attachment-test"]').text()).toBe('test');
    });

    it('should render message if attachments key is missing', async () => {
        const { attachments, ...differentMessage } = message;
        spyGetMessage.mockReturnValueOnce(Promise.resolve(differentMessage));
        const wrapper = await createComponent(true);

        expect(wrapper.find('.title').text()).toBe(message.subject);
        expect(wrapper.find('[data-testid="message-info"]').exists()).toBe(true);
    });
});
