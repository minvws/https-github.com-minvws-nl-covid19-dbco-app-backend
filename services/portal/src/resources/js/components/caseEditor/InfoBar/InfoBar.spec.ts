import { shallowMount } from '@vue/test-utils';
import InfoBar from './InfoBar.vue';
import { Store } from 'vuex';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { ContactTracingStatusV1 } from '@dbco/enum';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object, storeData?: Store<any>) => {
    localVue.component('FormInfo', FormInfo);

    return shallowMount(InfoBar, {
        localVue,
        propsData: props,
        store: storeData ?? new Store({}),
    });
});

describe('InfoBar.vue', () => {
    it('render warning message when status is completed', () => {
        const props = {};
        const storeData = new Store({
            modules: {
                index: {
                    namespaced: true,
                    getters: {
                        meta: vi.fn(() => ({
                            statusIndexContactTracing: ContactTracingStatusV1.VALUE_completed,
                        })),
                    },
                },
            },
        });

        const wrapper = createComponent(props, storeData);

        expect(wrapper.html()).toContain(
            'Dit BCO is afgerond. Het dossier in HPZone is mogelijk actueler. Voer eventuele wijzigingen die je hier maakt daarom ook in HPZone door.'
        );
    });

    it('should not render when status is completed', () => {
        const props = {};
        const storeData = new Store({
            modules: {
                index: {
                    namespaced: true,
                    getters: {
                        meta: vi.fn(() => ({
                            statusIndexContactTracing: ContactTracingStatusV1.VALUE_not_reachable,
                        })),
                    },
                },
            },
        });

        const wrapper = createComponent(props, storeData);

        expect(wrapper.html()).toEqual('');
    });
});
