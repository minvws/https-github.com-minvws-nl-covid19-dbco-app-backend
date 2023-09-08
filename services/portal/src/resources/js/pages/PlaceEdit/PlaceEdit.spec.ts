import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, setupTest } from '@/utils/test';
import PlaceEdit from './PlaceEdit.vue';
import i18n from '@/i18n/index';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(PlaceEdit, {
        localVue,
        i18n,
        propsData: {
            place: {
                label: fakerjs.company.name(),
                uuid: fakerjs.string.uuid(),
            },
        },
    });
});

describe('PlaceEdit.vue', () => {
    it('should render with place label as title', () => {
        // GIVEN a place through props
        // WHEN the page renders
        const wrapper = createComponent();

        // THEN it should render with place label as title
        const title = wrapper.find('[data-testid="place-label"]');
        expect(title.text()).toBe(wrapper.vm.$props.place.label);
    });
});
