import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { decorateWrapper, fakerjs, flushCallStack, setupTest } from '@/utils/test';
import BsnConnectedItems from './BsnConnectedItems.vue';
import { BsnLookupType } from '@/components/form/ts/formTypes';
import { caseApi, taskApi } from '@dbco/portal-api';

const relatedCases = [
    {
        uuid: fakerjs.string.uuid(),
        organisation: {
            uuid: fakerjs.string.uuid(),
            abbreviation: 'GGD1',
            isCurrent: true,
        },
        hasSymptoms: true,
        dateOfSymptomOnset: '2021-01-01',
        number: fakerjs.string.numeric(6),
        category: '1',
        dateOfLastExposure: null,
        relationship: null,
    },
    {
        uuid: fakerjs.string.uuid(),
        organisation: {
            uuid: fakerjs.string.uuid(),
            abbreviation: 'GGD1',
            isCurrent: true,
        },
        hasSymptoms: false,
        dateOfTest: '2021-03-01',
        number: fakerjs.string.numeric(6),
        category: '1',
    },
];

const relatedTasks = [
    {
        uuid: fakerjs.string.uuid(),
        organisation: {
            uuid: fakerjs.string.uuid(),
            abbreviation: 'GGD1',
            isCurrent: true,
        },
        dateOfSymptomOnset: '2021-06-01',
        dateOfLastExposure: '2021-04-01',
        number: fakerjs.string.numeric(6),
        category: '1',
        relationship: null,
    },
    {
        uuid: fakerjs.string.uuid(),
        organisation: {
            uuid: fakerjs.string.uuid(),
            abbreviation: 'GGD2',
            isCurrent: false,
        },
        number: fakerjs.string.numeric(6),
    },
];

vi.mock('@dbco/portal-api/client/task.api', () => ({
    getConnected: vi.fn(() => Promise.resolve({ cases: relatedCases, tasks: relatedTasks })),
}));

vi.mock('@dbco/portal-api/client/case.api', () => ({
    getConnected: vi.fn(() => Promise.resolve({ cases: relatedCases, tasks: relatedTasks })),
}));

const createComponent = setupTest((localVue: VueConstructor, propsData?: object) => {
    return shallowMount(BsnConnectedItems, { localVue, propsData });
});

describe('BsnConnectedItems.vue', () => {
    it('should show "Er zijn geen dossiers gevonden" if connectedItems.length === 0', async () => {
        vi.spyOn(caseApi, 'getConnected').mockImplementationOnce(() => Promise.resolve({ cases: [], tasks: [] }));

        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const noConnectedItemsElement = wrapper.findByTestId('no-cases-text');
        expect(noConnectedItemsElement.exists()).toBe(true);
    });

    it('should render connected-items for targetType Index', async () => {
        const spyOnGetConnected = vi.spyOn(caseApi, 'getConnected');
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const connectedCases = wrapper.findAllByTestId('connected-case');
        expect(connectedCases.length).toBe(4);
        expect(spyOnGetConnected).toHaveBeenCalledTimes(1);
    });

    it('should render connected-items for targetType Index', async () => {
        const spyOnGetConnected = vi.spyOn(taskApi, 'getConnected');
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Task };
        const wrapper = createComponent(props);
        await flushCallStack();

        const connectedCases = wrapper.findAllByTestId('connected-case');
        expect(connectedCases.length).toBe(4);
        expect(spyOnGetConnected).toHaveBeenCalledTimes(1);
    });

    it('should not show organisation abbreviation in connected case if organisation.isCurrent === false', async () => {
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const connectedCases = wrapper.findAllByTestId('connected-case');
        expect(connectedCases.at(0).text()).not.toContain('GGD1');
    });

    it('should show organisation abbreviation in connected case if organisation.isCurrent === true', async () => {
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const connectedCases = wrapper.findAllByTestId('connected-case');
        expect(connectedCases.at(3).text()).toContain('GGD2');
    });

    it('should show full category label if connected item is "task"', async () => {
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const taskCategoryLabel = wrapper.findByTestId('task-category-label');
        expect(taskCategoryLabel.text()).toContain('1 - Huisgenoot');
    });

    it('should show Eerste ziektedag if connectedItem is index and .hasSymtpoms === true', async () => {
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const taskCategoryLabel = wrapper.findByTestId('task-category-label');
        expect(taskCategoryLabel.text()).toContain('1 - Huisgenoot');
    });

    it('should show Eerste ziektedag if connectedItem is index and connectedItem.hasSymtpoms === true', async () => {
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const connectedCases = wrapper.findAllByTestId('connected-case');
        const nthCase = decorateWrapper(connectedCases.at(0));

        const relevantDateLabel = nthCase.findByTestId('relevant-date-label');
        const relevantDate = nthCase.findByTestId('relevant-date');

        expect(relevantDateLabel.text()).toContain('Eerste ziektedag');
        expect(relevantDate.text()).toContain('1 jan. 2021');
    });

    it('should show Testdatum if connectedItem is index and connectedItem.hasSymptoms === false', async () => {
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = await createComponent(props);
        await flushCallStack();

        const connectedCases = wrapper.findAllByTestId('connected-case');
        const nthCase = decorateWrapper(connectedCases.at(1));

        const relevantDateLabel = nthCase.findByTestId('relevant-date-label');
        const relevantDate = nthCase.findByTestId('relevant-date');

        expect(relevantDateLabel.text()).toContain('Testdatum');
        expect(relevantDate.text()).toContain('1 mrt. 2021');
    });

    it('should show Laatste contactdatum if connectedItem.type === "task"', async () => {
        const props = { uuid: fakerjs.string.uuid(), targetType: BsnLookupType.Index };
        const wrapper = createComponent(props);
        await flushCallStack();

        const connectedCases = wrapper.findAllByTestId('connected-case');
        const nthCase = decorateWrapper(connectedCases.at(2));

        const relevantDateLabel = nthCase.findByTestId('relevant-date-label');
        const relevantDate = nthCase.findByTestId('relevant-date');

        expect(relevantDateLabel.text()).toContain('Laatste contactdatum');
        expect(relevantDate.text()).toContain('1 apr. 2021');
    });
});
