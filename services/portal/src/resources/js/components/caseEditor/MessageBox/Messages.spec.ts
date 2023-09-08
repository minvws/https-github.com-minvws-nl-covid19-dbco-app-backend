import type { VueConstructor } from 'vue';
import type { SpyInstance } from 'vitest';
import { vi } from 'vitest';
import { fireEvent, render, waitFor } from '@testing-library/vue';

import indexStore from '@/store/index/indexStore';
import { fakerjs, setupTest } from '@/utils/test';
import { caseUuid, defaultMessages, generateMessageSummary } from './FakeMessage';
import { messageApi } from '@dbco/portal-api';
import Messages from './Messages.vue';
import type { MessageSummary } from '@dbco/portal-api/message.dto';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return render(Messages, {
        localVue,
        propsData: props,
        store: {
            modules: {
                index: {
                    ...indexStore,
                    state: {
                        ...indexStore.state,
                        uuid: caseUuid,
                    },
                },
            },
        },
    });
});

describe('Display Messages', () => {
    let spyGetMessages: SpyInstance;

    beforeEach(() => {
        spyGetMessages = vi.spyOn(messageApi, 'getMessages');
    });
    it('should show "no messages" notice if no messages are available', async () => {
        spyGetMessages.mockReturnValueOnce(
            Promise.resolve({
                messages: [],
            })
        );

        const wrapper = await createComponent();

        expect(await wrapper.findByTestId('no-messages')).toBeTruthy();
    });

    it('should show multiple messages for index if available', async () => {
        const messages = [generateMessageSummary(), generateMessageSummary()];
        spyGetMessages.mockReturnValueOnce(
            Promise.resolve({
                messages,
            })
        );

        const wrapper = await createComponent();
        await waitFor(() => expect(wrapper.getAllByRole('row').length).toBeGreaterThan(1));

        expect(wrapper.queryByText(messages[0].subject)).toBeTruthy();
        expect(wrapper.queryByText(messages[1].subject)).toBeTruthy();
    });

    it('should show multiple messages for contact if available', async () => {
        const contactUuid = fakerjs.string.uuid();
        spyGetMessages.mockReturnValueOnce(
            Promise.resolve({
                messages: [
                    generateMessageSummary(contactUuid),
                    generateMessageSummary(contactUuid),
                    generateMessageSummary(),
                ],
            })
        );

        const wrapper = await createComponent({
            taskUuid: contactUuid,
        });
        expect((await wrapper.findAllByRole('row')).length).toBe(3); // 2 rows + head
    });

    it('table cell should show message "Niet meer beschikbaar" if message.isExpired is true', async () => {
        const messages: MessageSummary[] = [
            {
                ...generateMessageSummary(),
                isExpired: true,
            },
        ];
        spyGetMessages.mockReturnValueOnce(Promise.resolve({ messages: messages }));

        const wrapper = await createComponent();
        expect(await wrapper.findByRole('row', { name: /Niet meer beschikbaar/i })).toBeTruthy();
    });

    it('table cell should show message "Bericht ingetrokken" if message.isDeleted is true', async () => {
        const messages: MessageSummary[] = [
            {
                ...generateMessageSummary(),
                isDeleted: true,
            },
        ];
        spyGetMessages.mockReturnValueOnce(Promise.resolve({ messages: messages }));

        const wrapper = await createComponent();

        expect(await wrapper.findByRole('row', { name: /Bericht ingetrokken/i })).toBeTruthy();
    });

    it('should select message when row is clicked', async () => {
        spyGetMessages.mockReturnValueOnce(
            Promise.resolve({
                messages: defaultMessages,
            })
        );

        const wrapper = await createComponent();
        await waitFor(() => {
            expect(wrapper.queryAllByRole('row').length).toBeGreaterThan(1);
        });

        await fireEvent.click(await wrapper.findByRole('row', { name: new RegExp(defaultMessages[0].subject) }));

        expect(wrapper.emitted()['select']).toEqual([[defaultMessages[0].uuid]]);
    });

    it('should show attachment icon when message has attachments', async () => {
        const messages: MessageSummary[] = [
            {
                ...generateMessageSummary(),
                hasAttachments: true,
            },
        ];
        spyGetMessages.mockReturnValueOnce(Promise.resolve({ messages: messages }));

        const wrapper = await createComponent();
        expect(await wrapper.findByTestId('icon-attachment')).toBeTruthy();
    });
});
