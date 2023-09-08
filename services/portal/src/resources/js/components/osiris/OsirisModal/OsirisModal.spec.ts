import { shallowMount } from '@vue/test-utils';
import OsirisModal from './OsirisModal.vue';
import type { VueConstructor } from 'vue/types/umd';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { useEventBus } from '@/composables/useEventBus';

import { getOsirisValidationStatusMessages } from '@dbco/portal-api/client/case.api';
import type { MockInstance } from 'vitest';
import type { CaseValidationMessages } from '@dbco/portal-api/case.dto';

import OsirisCaseStatus from '../OsirisCaseStatus/OsirisCaseStatus.vue';
import OsirisCaseValidationResult from '../OsirisCaseValidationResult/OsirisCaseValidationResult.vue';

vi.mock('@dbco/portal-api/client/case.api', () => ({
    getOsirisValidationStatusMessages: vi.fn(),
}));

type Props = {
    covidCase: AnyObject;
};

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        props: Props = {
            covidCase: {},
        }
    ) => {
        return shallowMount(OsirisModal, {
            localVue,
            propsData: props,
        });
    }
);

afterEach(() => {
    vi.clearAllMocks();
});

const eventBus = useEventBus();

function mockValidationsMessagesOnce(messages: Partial<CaseValidationMessages>) {
    (getOsirisValidationStatusMessages as unknown as MockInstance).mockImplementationOnce(() =>
        Promise.resolve({
            fatal: [],
            warning: [],
            notice: [],
            ...messages,
        })
    );
}

describe('OsirisModal.vue', () => {
    it('should load validation messages when openend', () => {
        createComponent();
        expect(getOsirisValidationStatusMessages).not.toHaveBeenCalled();

        eventBus.$emit('open-osiris-modal');

        expect(getOsirisValidationStatusMessages).toHaveBeenCalledOnce();
    });

    it.each([
        { fatal: [fakerjs.lorem.sentence()] },
        { warning: [fakerjs.lorem.sentence()] },
        { notice: [fakerjs.lorem.sentence()] },
    ])('should show validation messages if there are any', async (messages) => {
        mockValidationsMessagesOnce(messages);
        const wrapper = createComponent();
        eventBus.$emit('open-osiris-modal');
        await flushCallStack();
        expect(wrapper.findComponent(OsirisCaseValidationResult).exists()).toBe(true);
        expect(wrapper.findComponent(OsirisCaseStatus).exists()).toBe(false);
    });

    it('should continue straight to the case status if there are no validation messages', async () => {
        mockValidationsMessagesOnce({});
        const wrapper = createComponent();
        eventBus.$emit('open-osiris-modal');
        await flushCallStack();
        expect(wrapper.findComponent(OsirisCaseValidationResult).exists()).toBe(false);
        expect(wrapper.findComponent(OsirisCaseStatus).exists()).toBe(true);
    });

    it('should continue to the case status if when user chooses to', async () => {
        mockValidationsMessagesOnce({ warning: [fakerjs.lorem.sentence()] });
        const wrapper = createComponent();
        eventBus.$emit('open-osiris-modal');
        await flushCallStack();
        await wrapper.findComponent(OsirisCaseValidationResult).vm.$emit('ok', { preventDefault: vi.fn() });
        expect(wrapper.findComponent(OsirisCaseStatus).exists()).toBe(true);
    });

    it.only('should reset steps when modal is closed', async () => {
        mockValidationsMessagesOnce({ warning: [fakerjs.lorem.sentence()] });
        const wrapper = createComponent();
        eventBus.$emit('open-osiris-modal');
        expect(getOsirisValidationStatusMessages).toHaveBeenCalledTimes(1);

        await flushCallStack();
        await wrapper.findComponent(OsirisCaseValidationResult).vm.$emit('hidden');
        expect(wrapper.findComponent(OsirisCaseStatus).exists()).toBe(false);

        mockValidationsMessagesOnce({ notice: [fakerjs.lorem.sentence()] });
        eventBus.$emit('open-osiris-modal');
        await flushCallStack();

        expect(getOsirisValidationStatusMessages).toHaveBeenCalledTimes(2);
        expect(wrapper.findComponent(OsirisCaseValidationResult).exists()).toBe(true);
    });

    it('should clean up on unmount', () => {
        vi.spyOn(eventBus, '$off');
        const wrapper = createComponent();
        wrapper.destroy();
        expect(eventBus.$off).toHaveBeenCalledWith('open-osiris-modal', expect.any(Function));
    });
});
