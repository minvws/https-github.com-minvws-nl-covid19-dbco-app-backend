import { messageApi } from '@dbco/portal-api';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import BootstrapVue from 'bootstrap-vue';
import Vuex, { Store } from 'vuex';
import { MessageStatusV1, MessageTemplateTypeV1, PermissionV1 } from '@dbco/enum';
import FormSendEmail from './FormSendEmail.vue';
import { userCanEdit } from '@/utils/interfaceState';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import { flushCallStack } from '@/utils/test';
import { isEditCaseModulePath } from '@/utils/url';
import type { MessageSummary } from '@dbco/portal-api/message.dto';

vi.mock('@/utils/interfaceState');
vi.mock('@/utils/url');

const messages: MessageSummary[] = [
    {
        uuid: 'message-0000',
        mailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
        caseUuid: 'case-0000',
        taskUuid: null,
        toEmail: 'john.doe+index@example.com',
        toName: 'John Doe',
        telephone: null,
        subject: 'Belpoging van uw GGD betreft COVID-19',
        createdAt: '2022-01-01T12:00:00Z',
        notificationSentAt: null,
        expiresAt: '2022-02-01T12:00:00Z',
        status: MessageStatusV1.VALUE_draft,
        isExpired: false,
        isDeleted: false,
        identityRequired: false,
        isIdentified: false,
    },
    {
        uuid: 'message-0001',
        mailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
        caseUuid: 'case-0000',
        taskUuid: null,
        toEmail: 'john.doe+index@example.com',
        toName: 'John Doe',
        telephone: null,
        subject: 'Belpoging van uw GGD betreft COVID-19',
        createdAt: '2022-10-01T12:00:00Z',
        notificationSentAt: null,
        expiresAt: '2022-11-01T12:00:00Z',
        status: MessageStatusV1.VALUE_draft,
        isExpired: false,
        isDeleted: false,
        identityRequired: false,
        isIdentified: false,
    },
    {
        uuid: 'message-1111',
        mailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
        caseUuid: 'case-0000',
        taskUuid: 'contact-1111',
        toEmail: 'jane.doe+contact@example.com',
        toName: 'Jane Doe',
        telephone: null,
        subject: 'Belpoging van uw GGD betreft COVID-19',
        createdAt: '2022-02-01T12:00:00Z',
        notificationSentAt: null,
        expiresAt: '2022-03-01T12:00:00Z',
        status: MessageStatusV1.VALUE_draft,
        isExpired: false,
        isDeleted: false,
        identityRequired: false,
        isIdentified: false,
    },
];
vi.mock('@dbco/portal-api/client/message.api', async () => {
    return {
        getMessages: vi.fn(() =>
            Promise.resolve({
                messages,
            })
        ),
    };
});

describe('FormSendEmail.vue', () => {
    const datetimeFormatLongMock = vi.fn();

    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(Vuex);

    const getWrapper = (
        props?: object,
        data: object = {},
        indexStoreState: Partial<IndexStoreState> = {},
        userInfoStoreState: Partial<UserInfoState> = {},
        rootModel: object = {}
    ) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoStoreState,
            },
        };

        return shallowMount(FormSendEmail, {
            localVue,
            propsData: props,
            data: () => data,
            stubs: {
                FormulateInput: true,
            },
            store: new Store({
                modules: {
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            provide: {
                rootModel: () => rootModel,
            },
            mocks: {
                $filters: {
                    dateTimeFormatLong: datetimeFormatLongMock,
                },
            },
        });
    };

    it('should disable SendEmailButton if disabled prop is true', () => {
        const buttonLabel = 'Testlabel';

        const props = {
            disabled: true,
            caseUuid: '5D756A1F-6779-4577-8E9B-04EE76D4CD31',
            taskUuid: '75D75763-61C2-48FB-B804-AB27F4172C7B',
            ctaLabel: 'LABEL',
            emailVariant: MessageTemplateTypeV1.VALUE_personalAdvice,
            storeName: 'index',
            buttonLabel,
            buttonVariant: 'outline-primary',
        };
        const data = {};
        const rootModel = {};

        const wrapper = getWrapper(props, data, {}, {}, rootModel);

        expect(wrapper.findAll("[data-testid='send-email-button']").at(0).attributes().isdisabled).toBe('true');
    });

    it('should show a label and chevron-right icon if buttonVariant is "link"', () => {
        const buttonLabel = 'Testlabel';

        const props = {
            caseUuid: '0000',
            buttonLabel,
            buttonVariant: 'link',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            storeName: 'index',
        };

        const wrapper = getWrapper(props);

        const button = wrapper.findComponent({ name: 'SendEmailButton' });
        const icon = wrapper.get('chevronrighticon-stub');

        expect(button.text()).toBe(buttonLabel);
        expect(icon.exists()).toBe(true);
    });

    it('should only show a label if buttonVariant is not "link"', () => {
        const buttonLabel = 'Testlabel';

        const props = {
            caseUuid: '0000',
            buttonLabel,
            buttonVariant: 'outline-primary',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            storeName: 'index',
        };

        const wrapper = getWrapper(props);

        const button = wrapper.findComponent({ name: 'SendEmailButton' });
        const icon = wrapper.findComponent({ name: 'chevronrighticon-stub' });

        expect(button.text()).toBe(buttonLabel);
        expect(icon.exists()).toBe(false);
    });

    it.each([
        {
            description: 'should show *latest* email-sent if same emailVariant was sent for index',
            caseUuid: 'case-0000',
            taskUuid: null,
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            expectedVisibility: true,
            expectedDate: '2022-10-01T12:00:00Z',
        },
        {
            description: 'should NOT show email-sent if different emailVariant was sent for index',
            caseUuid: 'case-0000',
            taskUuid: null,
            emailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
            expectedVisibility: false,
            expectedDate: null,
        },
        {
            description: 'should NOT show email-sent if same emailVariant was sent for the CONTACT of index',
            caseUuid: 'case-1111',
            taskUuid: null,
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            expectedVisibility: false,
            expectedDate: null,
        },
        {
            description: 'should show email-sent if same emailVariant was sent for contact',
            caseUuid: 'case-0000',
            taskUuid: 'contact-1111',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            expectedVisibility: true,
            expectedDate: '2022-02-01T12:00:00Z',
        },
        {
            description: 'should NOT show email-sent if different emailVariant was sent for contact',
            caseUuid: 'case-0000',
            taskUuid: 'contact-1111',
            emailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
            expectedVisibility: false,
            expectedDate: null,
        },
        {
            description: 'should NOT show email-sent if same emailVariant was sent for different contact',
            caseUuid: 'case-0000',
            taskUuid: 'contact-2222',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            expectedVisibility: false,
            expectedDate: null,
        },
    ])('$description', async ({ caseUuid, taskUuid, emailVariant, expectedVisibility, expectedDate }) => {
        const props = {
            caseUuid,
            taskUuid,
            buttonLabel: 'Testlabel',
            buttonVariant: 'outline-primary',
            emailVariant,
            storeName: 'index',
        };

        (userCanEdit as Mock).mockImplementation(() => true);
        const userInfoState: Partial<UserInfoState> = { permissions: [PermissionV1.VALUE_caseUserEdit] };

        const wrapper = getWrapper(props, {}, {}, userInfoState);
        await flushCallStack();

        const emailSent = wrapper.find('.email-sent');
        expect(emailSent.exists()).toBe(expectedVisibility);

        if (expectedDate) {
            expect(datetimeFormatLongMock).toHaveBeenCalledWith(expectedDate);
        }
    });

    it('should show sent-email notification if user has userEditPermission and emailSent is true', async () => {
        const buttonLabel = 'Testlabel';

        const props = {
            disabled: true,
            caseUuid: 'case-0000',
            ctaLabel: 'LABEL',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            storeName: 'index',
            buttonLabel,
            buttonVariant: 'outline-primary',
        };
        const data = {};
        const rootModel = {};

        (userCanEdit as Mock).mockImplementation(() => true);
        const userInfoState: Partial<UserInfoState> = { permissions: [PermissionV1.VALUE_caseUserEdit] };

        const wrapper = getWrapper(props, data, {}, userInfoState, rootModel);

        // Add tick to fetch messages
        await flushCallStack();

        expect(wrapper.find('.email-sent').exists()).toBe(true);
    });

    it('should not show sent-email notification if userCanEdit is false and emailSent is true', async () => {
        const buttonLabel = 'Testlabel';

        const props = {
            disabled: true,
            caseUuid: 'case-0000',
            ctaLabel: 'LABEL',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            storeName: 'index',
            buttonLabel,
            buttonVariant: 'outline-primary',
        };
        const data = {};
        const rootModel = {};

        (userCanEdit as Mock).mockImplementation(() => false);
        (isEditCaseModulePath as Mock).mockImplementation(() => true);

        const wrapper = getWrapper(props, data, {}, rootModel);

        // Add tick to fetch messages
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.email-sent').exists()).toBe(false);
    });

    it('should not show sent-email notification if userCanEdit is false and emailSent is false', async () => {
        const buttonLabel = 'Testlabel';

        const props = {
            disabled: true,
            caseUuid: 'case-0000',
            ctaLabel: 'LABEL',
            emailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
            storeName: 'index',
            buttonLabel,
            buttonVariant: 'outline-primary',
        };
        const data = {};
        const rootModel = {};

        (userCanEdit as Mock).mockImplementation(() => false);
        (isEditCaseModulePath as Mock).mockImplementation(() => true);

        const wrapper = getWrapper(props, data, {}, rootModel);

        // Add tick to fetch messages
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.email-sent').exists()).toBe(false);
    });

    it('should fire this.getMessages when userCanEdit is true', async () => {
        const buttonLabel = 'Testlabel';

        const props = {
            disabled: true,
            caseUuid: 'case-0000',
            ctaLabel: 'LABEL',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            storeName: 'index',
            buttonLabel,
            buttonVariant: 'outline-primary',
        };
        const data = {};
        const rootModel = {};

        (userCanEdit as Mock).mockImplementation(() => true);
        (isEditCaseModulePath as Mock).mockImplementation(() => false);
        const mockApi = vi.spyOn(messageApi, 'getMessages');
        const wrapper = getWrapper(props, data, {}, rootModel);

        // Add tick to fetch messages
        await wrapper.vm.$nextTick();

        expect(mockApi).toHaveBeenCalledTimes(1);
    });

    it('should not fire this.getMessages when userCanEdit is false', async () => {
        const buttonLabel = 'Testlabel';

        const props = {
            disabled: true,
            caseUuid: 'case-0000',
            ctaLabel: 'LABEL',
            emailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
            storeName: 'index',
            buttonLabel,
            buttonVariant: 'outline-primary',
        };
        const data = {};
        const rootModel = {};

        (userCanEdit as Mock).mockImplementation(() => false);
        (isEditCaseModulePath as Mock).mockImplementation(() => true);
        const mockApi = vi.spyOn(messageApi, 'getMessages');
        const wrapper = getWrapper(props, data, {}, rootModel);

        // Add tick to fetch messages
        await wrapper.vm.$nextTick();

        expect(mockApi).toHaveBeenCalledTimes(0);
    });
});
