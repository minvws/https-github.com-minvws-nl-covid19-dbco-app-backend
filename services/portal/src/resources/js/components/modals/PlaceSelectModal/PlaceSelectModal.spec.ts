import contextStore from '@/store/context/contextStore';
import indexStore from '@/store/index/indexStore';
import placeStore from '@/store/place/placeStore';
import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import PlaceSelectModal from './PlaceSelectModal.vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(PlaceSelectModal, {
        localVue,
        store: new Vuex.Store({
            modules: {
                context: {
                    ...contextStore,
                    state: {
                        uuid: 'context-uuid',
                        place: null,
                    },
                },
                index: indexStore,
                place: placeStore,
            },
        }),
    });
});

describe('PlaceSelectModal.vue', () => {
    beforeEach(() => {
        vi.resetAllMocks();
    });

    it('should initiate visible', () => {
        const wrapper = createComponent();

        expect(wrapper.find('.location-select').exists()).toBe(true);
    });
});
