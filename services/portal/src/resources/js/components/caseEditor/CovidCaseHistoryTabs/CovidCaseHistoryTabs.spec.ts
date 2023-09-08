import { shallowMount } from '@vue/test-utils';
import CovidCaseHistoryTabs from './CovidCaseHistoryTabs.vue';
import { fakerjs, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import i18n from '@/i18n/index';
import { Tab } from '@dbco/ui-library';

const caseOsirisNumber = fakerjs.number.int();
const caseUuid = fakerjs.string.uuid();

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount<CovidCaseHistoryTabs>(CovidCaseHistoryTabs, {
        localVue,
        i18n,
        propsData: {
            caseOsirisNumber,
            caseUuid,
        },
    });
});

describe('CovidCaseHistoryTabs.vue', () => {
    it('should render CovidCaseHistoryTabs', () => {
        const wrapper = createComponent();

        const tabs = wrapper.findAllComponents(Tab);
        expect(tabs.at(0).text()).toBe(i18n.t('components.covidCaseHistory.tabs.default'));
        expect(tabs.at(1).text()).toBe(i18n.t('components.covidCaseHistory.tabs.osiris'));
    });
});
