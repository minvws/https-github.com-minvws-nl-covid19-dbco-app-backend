import Vuex from 'vuex';

import { mount } from '@vue/test-utils';

import ContactEditingModal from './ContactEditingModal.vue';
import type { TaskStoreState } from '@/store/task/taskStore';
import taskStore from '@/store/task/taskStore';

import { InformStatusV1 } from '@dbco/enum';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';

import * as FormSchemaFunctions from '@/components/form/ts/formSchema';
import { flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const mockedLoadTask = vi.fn();
const mockedAction = vi.fn();

const aSchema = {
    tabs: [
        {
            type: 'tab',
            id: 'about',
            title: 'Over de index',
            schema: () => vi.fn(),
        },
        {
            type: 'tab',
            id: 'medical',
            title: 'Medisch',
            schema: () => vi.fn(),
        },
    ],
    version: 3,
    sidebar: vi.fn(() => []),
    rules: {
        index: [],
        context: [],
        task: [],
    },
};

vi.mock('@/components/form/ts/formSchema', () => ({
    getRootSchema: vi.fn(() => {
        return {
            tabs: [
                {
                    type: 'tab',
                    id: 'about',
                    title: 'Over de index',
                    schema: () => vi.fn(),
                },
                {
                    type: 'tab',
                    id: 'medical',
                    title: 'Medisch',
                    schema: () => vi.fn(),
                },
            ],

            sidebar: vi.fn(() => []),
            rules: {
                index: [],
            },
        };
    }),
    getSchema: vi.fn((type) => {
        return [];
    }),
}));

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        taskStoreData: Partial<TaskStoreState>,
        userInfoStoreData?: Partial<UserInfoState>,
        indexStoreData?: Partial<IndexStoreState>
    ) => {
        const taskStoreState = {
            ...taskStore,
            state: {
                ...(taskStore as any).state,
                ...taskStoreData,
            },
            actions: {
                LOAD: mockedLoadTask,
                FETCH_TASKS: mockedAction,
            },
        };

        const userStoreState = {
            ...userInfoStore,
            state: {
                ...(userInfoStore as any).state,
                ...userInfoStoreData,
            },
            actions: {
                LOAD: mockedAction,
            },
        };

        const indexStoreState = {
            ...indexStore,
            state: {
                ...(indexStore as any).state,
                ...indexStoreData,
            },
            actions: {
                LOAD: mockedAction,
            },
        };

        return mount(ContactEditingModal, {
            localVue,
            stubs: {
                FormRenderer: true,
                LastUpdated: true,
                CovidCaseSidebar: true,
            },
            store: new Vuex.Store({
                modules: {
                    task: taskStoreState,
                    userInfo: userStoreState,
                    index: indexStoreState,
                },
            }),
        });
    }
);

describe('ContactEditingModal.vue', () => {
    it.each([
        [InformStatusV1.VALUE_uninformed, 'Nog niet geïnformeerd'],
        [InformStatusV1.VALUE_unreachable, 'Geen gehoor'],
        [InformStatusV1.VALUE_emailSent, 'Alleen gemaild'],
        [InformStatusV1.VALUE_informed, 'Geïnformeerd'],
    ])('getContentForStatus() method should return text "%s" when TASK_STATUS is %s', async (informStatus, text) => {
        const taskStoreState = {
            fragments: {
                general: {
                    label: '',
                },
                inform: {
                    status: informStatus,
                },
            },
        };
        const indexStoreState = {};
        const wrapper = createComponent(taskStoreState, {}, indexStoreState);

        await flushCallStack();

        const informStatusEl = wrapper.find('[data-testid="inform-status"');
        expect(informStatusEl.text()).toContain(text);
    });

    it('Should call getRootSchema and dispatch "task/LOAD" on created', async () => {
        const taskStoreState = {
            fragments: {
                general: {
                    label: '',
                },
                inform: {
                    status: InformStatusV1.VALUE_uninformed,
                },
            },
        };
        const indexStoreState = {};
        createComponent(taskStoreState, {}, indexStoreState);

        const spyGetRootSchema = vi.spyOn(FormSchemaFunctions, 'getRootSchema').mockImplementationOnce(() => aSchema);

        await flushCallStack();

        expect(spyGetRootSchema).toHaveBeenCalled();
        expect(mockedLoadTask).toHaveBeenCalled();
    });

    it('Should emit "onClose" when this.closeModal() is being called', async () => {
        const taskStoreState = {
            fragments: {
                general: {
                    label: '',
                },
                inform: {
                    status: InformStatusV1.VALUE_uninformed,
                },
            },
        };
        const indexStoreState = {};
        const wrapper = createComponent(taskStoreState, {}, indexStoreState);

        await flushCallStack();

        wrapper.vm.closeModal();
        expect(wrapper.emitted().onClose).toBeTruthy();
    });
});
