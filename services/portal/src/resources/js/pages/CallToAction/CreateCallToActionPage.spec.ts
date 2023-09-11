import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import CreateCallToActionPage from './CreateCallToActionPage.vue';
import i18n from '@/i18n/index';
import { SharedActions } from '@/store/actions';
import indexStore from '@/store/index/indexStore';
import { Link } from '@dbco/ui-library';
import Vuex from 'vuex';

vi.mock('@/env');

const mockedAction = vi.fn();

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(CreateCallToActionPage, {
        localVue,
        i18n,
        propsData: {
            caseUuid: fakerjs.string.uuid(),
        },
        store: new Vuex.Store({
            modules: {
                index: {
                    ...indexStore,
                    state: {
                        ...indexStore.state,
                    },
                    actions: {
                        [SharedActions.LOAD]: mockedAction,
                    },
                },
            },
        }),
        stubs: { Link },
    });
});

describe('CreateCallToActionPage.vue', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('should show content', () => {
        // WHEN the page is rendered
        const wrapper = createComponent();

        // THEN its content should be rendered
        expect(wrapper.find('h1').exists()).toBe(true);
        expect(wrapper.find('h1').text()).toBe(i18n.t('pages.createCallToAction.title'));
    });

    it('should load index data in store when the component is created', async () => {
        // WHEN the component is created
        createComponent();
        await flushCallStack();

        // THEN it should have loaded index data in the vuex store
        expect(mockedAction).toHaveBeenCalledTimes(1);
    });

    it('should show a link to return to the case', () => {
        // WHEN the page is rendered
        const wrapper = createComponent();

        // THEN it should render a link to return to the case
        const returnLink = wrapper.find('.return-bar').findComponent(Link);

        expect(returnLink.exists()).toBe(true);
        expect(returnLink.text()).toBe(i18n.t('pages.createCallToAction.return'));
        expect(returnLink.attributes('href')).toBe(`/editcase/${wrapper.vm.$props.caseUuid}`);
    });

    it('should redirect to the case if CreateCallToAction component emits cancel event', async () => {
        // WHEN the page is rendered
        const wrapper = createComponent();

        // THEN it should redirect to the case if CreateCallToAction emits cancel event
        await wrapper.find('createcalltoaction-stub').vm.$emit('cancel');

        expect(window.location.replace).toHaveBeenCalled();
    });

    it('should redirect to the case if CreateCallToAction component emits created event', async () => {
        // WHEN the page is rendered
        const wrapper = createComponent();

        // THEN it should redirect to the case if CreateCallToAction emits cancel event
        await wrapper.find('createcalltoaction-stub').vm.$emit('created');

        expect(window.location.replace).toHaveBeenCalled();
    });
});
